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

namespace PrestaShop\Module\PrestashopCheckout\Order\Factory;

use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;
use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\Resume\Resume;
use PrestaShop\Module\PrestashopCheckout\Order\Resume\ResumeCart;
use PrestaShop\Module\PrestashopCheckout\Order\Resume\ResumeOrder;
use PrestaShop\Module\PrestashopCheckout\Order\Resume\ResumePayPalAuthorization;
use PrestaShop\Module\PrestashopCheckout\Order\Resume\ResumePayPalCapture;
use PrestaShop\Module\PrestashopCheckout\Order\Resume\ResumePayPalOrder;
use PrestaShop\Module\PrestashopCheckout\Order\Resume\ResumePayPalRefund;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQuery;
use PrestaShop\Module\PrestashopCheckout\Order\State\ValueObject\OrderStateId;

class OrderResumeFactory
{
    const PAYPAL_CAPTURE = 'PAYPAL_CAPTURE';
    const PAYPAL_REFUND = 'PAYPAL_REFUND';

    const PAYPAL_AUTHORIZATION = 'PAYPAL_AUTHORIZATION';

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @param CommandBusInterface $commandBus
     */
    public function __construct($commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param $cartId
     * @param $type
     * @param $status
     * @param $amount
     * @param $paypalOrderOldStatus
     * @param $paypalOrderNewStatus
     *
     * @return Resume
     *
     * @throws OrderException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException
     * @throws \PrestaShop\Module\PrestashopCheckout\Order\State\Exception\OrderStateException
     */
    public function create($cartId, $type, $status, $amount, $paypalOrderOldStatus, $paypalOrderNewStatus)
    {
        /** @var GetOrderResult $queryResult */
        $queryResult = $this->commandBus->handle(new GetOrderQuery($cartId));
        $order = new \Order($queryResult->id);
        $cart = new \Cart($cartId);
        $getOrderStateConfiguration = $this->commandBus->handle(new GetOrderStateConfigurationQuery());
        $orderStateId = new OrderStateId($order->getCurrentState());
        $total_refund = 0;
        foreach ($order->getOrderSlipsCollection() as $orderSlip) {
            $total_refund += $orderSlip->amount;
        }

        switch ($type) {
            case self::PAYPAL_CAPTURE:
                $resumeOrder = new Resume(
                    new ResumeCart(new CartId($cart->id), $cart->getOrderTotal()),
                    new ResumeOrder($getOrderStateConfiguration->getKeyById($orderStateId), $orderStateId, $order->total_paid_real, $order->total_paid, $total_refund),
                    new ResumePayPalOrder($paypalOrderOldStatus, $paypalOrderNewStatus),
                    new ResumePayPalCapture($status, $amount),
                    null,
                    null
                );
                break;
            case self::PAYPAL_REFUND:
                $resumeOrder = new Resume(
                    new ResumeCart(new CartId($cart->id), $cart->getOrderTotal()),
                    new ResumeOrder($getOrderStateConfiguration->getKeyById($orderStateId), $orderStateId, $order->total_paid_real, $order->total_paid, $total_refund),
                    new ResumePayPalOrder($paypalOrderOldStatus, $paypalOrderNewStatus),
                    null,
                    new ResumePayPalRefund($status, $amount),
                    null
                );
                break;
            case self::PAYPAL_AUTHORIZATION:
                $resumeOrder = new Resume(
                    new ResumeCart(new CartId($cart->id), $cart->getOrderTotal()),
                    new ResumeOrder($getOrderStateConfiguration->getKeyById($orderStateId), $orderStateId, $order->total_paid_real, $order->total_paid, $total_refund),
                    new ResumePayPalOrder($paypalOrderOldStatus, $paypalOrderNewStatus),
                    null,
                    null,
                    new ResumePayPalAuthorization($status, $amount)
                );
                break;
            default:
                throw new OrderException(sprintf('PayPal Order State invalid ("%s")', $type), OrderException::INVALID_PAYPAL_ORDER_STATE);
        }

        return $resumeOrder;
    }
}
