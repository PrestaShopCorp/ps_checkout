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

namespace PsCheckout\Core\PayPal\Order\Validator;

use Exception;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalAuthorizationStatus;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalCaptureStatus;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Exception\CartAlreadyPaidException;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderCaptureRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Repository\ReadOnlyCollectionRepositoryInterface;
use Psr\Log\LoggerInterface;

class PaidCartValidator implements PaidCartValidatorInterface
{
    /**
     * @var ReadOnlyCollectionRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var PayPalOrderCaptureRepositoryInterface
     */
    private $payPalOrderCaptureRepository;

    /**
     * @var PayPalOrderProviderInterface
     */
    private $payPalOrderProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ReadOnlyCollectionRepositoryInterface $orderRepository,
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        PayPalOrderCaptureRepositoryInterface $payPalOrderCaptureRepository,
        PayPalOrderProviderInterface $payPalOrderProvider,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->payPalOrderCaptureRepository = $payPalOrderCaptureRepository;
        $this->payPalOrderProvider = $payPalOrderProvider;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(int $cartId)
    {
        if (!empty($this->orderRepository->getAllBy(['id_cart' => $cartId]))) {
            $this->logger->warning('PayPal order not created: the cart already has an order', ['cart_id' => $cartId]);

            throw new CartAlreadyPaidException('Cart already has an order');
        }

        $previousPayPalOrder = $this->payPalOrderRepository->getOneByCartId($cartId);

        if (!$previousPayPalOrder || in_array($previousPayPalOrder->getStatus(), [PayPalOrderStatus::CANCELED, PayPalOrderStatus::VOIDED], true)) {
            return;
        }

        $context = ['cart_id' => $cartId, 'paypal_order_id' => $previousPayPalOrder->getId()];
        $payPalOrderResponse = null;

        // The local status can be late: a capture that timed out on our side may be completed at PayPal.
        try {
            $payPalOrderResponse = $this->payPalOrderProvider->getById($previousPayPalOrder->getId());
        } catch (Exception $exception) {
            $this->logger->warning('Previous PayPal order of the cart could not be read from PayPal', $context + ['exception' => $exception]);
        }

        $isPaid = $payPalOrderResponse
            ? $this->isPaidAtPayPal($payPalOrderResponse)
            : $this->isPaidLocally($previousPayPalOrder->getId());

        if (!$isPaid) {
            return;
        }

        $this->logger->warning('PayPal order not created: the previous PayPal order of the cart is already paid', $context);

        throw new CartAlreadyPaidException('Previous PayPal order of the cart is already paid', $payPalOrderResponse);
    }

    /**
     * A COMPLETED PayPal order is not always paid: a declined capture also completes the order.
     *
     * @param PayPalOrderResponse $payPalOrderResponse
     *
     * @return bool
     */
    private function isPaidAtPayPal(PayPalOrderResponse $payPalOrderResponse): bool
    {
        if ($payPalOrderResponse->getStatus() !== PayPalOrderStatus::COMPLETED) {
            return false;
        }

        foreach ($payPalOrderResponse->getCaptures() as $capture) {
            if (in_array($capture['status'] ?? '', [PayPalCaptureStatus::COMPLETED, PayPalCaptureStatus::PENDING], true)) {
                return true;
            }
        }

        foreach ($payPalOrderResponse->getAuthorizations() as $authorization) {
            if (in_array($authorization['status'], [PayPalAuthorizationStatus::CREATED, PayPalAuthorizationStatus::PENDING, PayPalAuthorizationStatus::CAPTURED, PayPalAuthorizationStatus::PARTIALLY_CAPTURED], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Used when PayPal cannot be read: only a capture saved by the module proves the payment.
     *
     * @param string $payPalOrderId
     *
     * @return bool
     */
    private function isPaidLocally(string $payPalOrderId): bool
    {
        foreach ($this->payPalOrderCaptureRepository->getByOrderId($payPalOrderId) as $capture) {
            if (in_array($capture->getStatus(), [PayPalCaptureStatus::COMPLETED, PayPalCaptureStatus::PENDING], true)) {
                return true;
            }
        }

        return false;
    }
}
