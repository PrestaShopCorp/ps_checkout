<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PsCheckout\Core\PayPal\Order\Action;

use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Handler\EventHandlerInterface;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Repository\CartRepositoryInterface;
use PsCheckout\Infrastructure\Repository\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Safety net for the client-side cancel race: a buyer-side cancel only flags the local record CANCELED while the
 * PayPal order stays payable. The `CHECKOUT.ORDER.APPROVED` webhook normally resumes the flow, this action covers
 * a lost or late webhook by asking PayPal directly.
 */
class ReconcileCanceledPayPalOrdersAction implements ReconcileCanceledPayPalOrdersActionInterface
{
    /**
     * PayPal approvals expire, so looking further back than the order expiry window is pointless.
     */
    const LOOKBACK_SECONDS = PayPalOrder::PAYPAL_ORDER_EXPIRY_SECONDS;

    /**
     * Upper bound of PayPal API calls per run.
     */
    const MAX_ORDERS_PER_RUN = 20;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var PayPalOrderProviderInterface
     */
    private $payPalOrderProvider;

    /**
     * @var PayPalOrderCacheInterface
     */
    private $payPalOrderCache;

    /**
     * @var EventHandlerInterface
     */
    private $orderApprovedEventHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $cartRepository,
        ContextInterface $context,
        PayPalOrderProviderInterface $payPalOrderProvider,
        PayPalOrderCacheInterface $payPalOrderCache,
        EventHandlerInterface $orderApprovedEventHandler,
        LoggerInterface $logger
    ) {
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
        $this->context = $context;
        $this->payPalOrderProvider = $payPalOrderProvider;
        $this->payPalOrderCache = $payPalOrderCache;
        $this->orderApprovedEventHandler = $orderApprovedEventHandler;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(): int
    {
        $since = new \DateTimeImmutable('-' . self::LOOKBACK_SECONDS . ' seconds');
        $resumed = 0;

        foreach ($this->payPalOrderRepository->getCanceledSince($since, self::MAX_ORDERS_PER_RUN) as $payPalOrder) {
            try {
                if ($this->reconcile($payPalOrder)) {
                    ++$resumed;
                }
            } catch (\Throwable $exception) {
                $this->logger->error('Failed to reconcile canceled PayPal order', [
                    'PayPalOrderId' => $payPalOrder->getId(),
                    'id_cart' => $payPalOrder->getIdCart(),
                    'exception' => $exception,
                ]);
            }
        }

        return $resumed;
    }

    /**
     * @param PayPalOrder $payPalOrder
     *
     * @return bool true when the approval flow was resumed
     */
    private function reconcile(PayPalOrder $payPalOrder): bool
    {
        if (!empty($this->orderRepository->getAllBy(['id_cart' => $payPalOrder->getIdCart()]))) {
            $this->markReconciled($payPalOrder);

            return false;
        }

        $cart = $this->cartRepository->getOneBy(['id_cart' => $payPalOrder->getIdCart()]);

        if (!$cart || !$cart->id) {
            $this->markReconciled($payPalOrder);

            return false;
        }

        // Same bootstrap as the webhook dispatcher: there is no buyer session behind this call.
        $this->context->loadCartForWebhook($cart);

        // Bypass the cache: only the live PayPal status matters here.
        if ($this->payPalOrderCache->has($payPalOrder->getId())) {
            $this->payPalOrderCache->delete($payPalOrder->getId());
        }

        $payPalOrderResponse = $this->payPalOrderProvider->getById($payPalOrder->getId());
        $payPalStatus = $payPalOrderResponse->getStatus();

        if ($payPalStatus === PayPalOrderStatus::COMPLETED) {
            // Captured outside of this module flow while no PrestaShop order exists: needs a human.
            $this->logger->warning('PayPal order captured but no PrestaShop order exists for the cart', [
                'PayPalOrderId' => $payPalOrder->getId(),
                'id_cart' => $payPalOrder->getIdCart(),
            ]);
            $this->markReconciled($payPalOrder);

            return false;
        }

        if ($payPalStatus !== PayPalOrderStatus::APPROVED) {
            // The buyer may still be completing the payment (CREATED, PAYER_ACTION_REQUIRED...): check again on the
            // next run, until the record leaves the lookback window. Only terminal statuses close the record.
            if (in_array($payPalStatus, [PayPalOrderStatus::VOIDED, PayPalOrderStatus::REVERSED], true)) {
                $this->markReconciled($payPalOrder);
            }

            return false;
        }

        $this->logger->info('Resuming PayPal order approved after a client-side cancel', [
            'PayPalOrderId' => $payPalOrder->getId(),
            'id_cart' => $payPalOrder->getIdCart(),
        ]);

        $this->orderApprovedEventHandler->handle($payPalOrderResponse);

        return true;
    }

    /**
     * @param PayPalOrder $payPalOrder
     */
    private function markReconciled(PayPalOrder $payPalOrder)
    {
        $payPalOrder->addTag(PayPalOrder::RECONCILED);
        $this->payPalOrderRepository->save($payPalOrder);
    }
}
