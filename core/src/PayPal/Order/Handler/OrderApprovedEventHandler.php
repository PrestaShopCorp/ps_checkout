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

namespace PsCheckout\Core\PayPal\Order\Handler;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Validator\OrderAuthorizationValidatorInterface;
use PsCheckout\Core\PayPal\Order\Action\CapturePayPalOrderActionInterface;
use PsCheckout\Core\PayPal\Order\Action\UpdatePayPalOrderPurchaseUnitActionInterface;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\OrderStatus\Action\PayPalCheckOrderStatusActionInterface;
use Psr\Log\LoggerInterface;

class OrderApprovedEventHandler implements EventHandlerInterface
{
    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var PayPalCheckOrderStatusActionInterface
     */
    private $checkPayPalOrderStatusAction;

    /**
     * @var OrderAuthorizationValidatorInterface
     */
    private $orderAuthorizationValidator;

    /**
     * @var CapturePayPalOrderActionInterface
     */
    private $capturePayPalOrderAction;

    /**
     * @var UpdatePayPalOrderPurchaseUnitActionInterface
     */
    private $updatePayPalOrderPurchaseUnit;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        PayPalCheckOrderStatusActionInterface $checkPayPalOrderStatusAction,
        OrderAuthorizationValidatorInterface $orderAuthorizationValidator,
        CapturePayPalOrderActionInterface $capturePayPalOrderAction,
        UpdatePayPalOrderPurchaseUnitActionInterface $updatePayPalOrderPurchaseUnit,
        LoggerInterface $logger
    ) {
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->checkPayPalOrderStatusAction = $checkPayPalOrderStatusAction;
        $this->orderAuthorizationValidator = $orderAuthorizationValidator;
        $this->capturePayPalOrderAction = $capturePayPalOrderAction;
        $this->updatePayPalOrderPurchaseUnit = $updatePayPalOrderPurchaseUnit;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(PayPalOrderResponse $payPalOrderResponse)
    {
        $payPalOrder = $this->payPalOrderRepository->getOneBy(['id' => $payPalOrderResponse->getId()]);

        if (!$payPalOrder) {
            throw new PsCheckoutException('PayPal order not found.', PsCheckoutException::ORDER_NOT_FOUND);
        }

        if (!$this->checkPayPalOrderStatusAction->execute($payPalOrder->getStatus(), PayPalOrderStatus::APPROVED)) {
            return;
        }

        $payPalOrder->setStatus($payPalOrderResponse->getStatus());

        $this->payPalOrderRepository->save($payPalOrder);
        $this->updatePayPalOrderPurchaseUnit->execute($payPalOrderResponse);

        if (
            $payPalOrder->isExpressCheckout()
            || $payPalOrder->hasTag(PayPalOrder::DELETED)
            || in_array($payPalOrder->getStatus(), [PayPalOrderStatus::COMPLETED, PayPalOrderStatus::CANCELED], true)) {
            return;
        }

        try {
            $this->orderAuthorizationValidator->validate($payPalOrder->getIdCart(), $payPalOrderResponse);
        } catch (PsCheckoutException $e) {
            $this->logger->warning(
                'PayPal order approved but not captured: authorization validation failed',
                [
                    'paypal_order_id' => $payPalOrderResponse->getId(),
                    'cart_id' => $payPalOrder->getIdCart(),
                    'exception_code' => $e->getCode(),
                    'exception_message' => $e->getMessage(),
                ]
            );

            return;
        }

        $this->capturePayPalOrderAction->execute($payPalOrderResponse);
    }
}
