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

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CancelPayPalOrderRequest;

class CancelPayPalOrderAction implements CancelPayPalOrderActionInterface
{
    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    public function __construct(PayPalOrderRepositoryInterface $payPalOrderRepository)
    {
        $this->payPalOrderRepository = $payPalOrderRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(CancelPayPalOrderRequest $cancelOrderRequest)
    {
        $paypalOrder = $this->payPalOrderRepository->getOneBy(['id' => $cancelOrderRequest->getOrderId()]);

        if (!$paypalOrder) {
            throw new PsCheckoutException(sprintf('Unable to update PrestaShop Checkout session #%s', var_export($cancelOrderRequest->getCartId(), true)), PsCheckoutException::UPDATE_FAILED);
        }

        // NOTE: the PayPal Order must be linked to the cart of the request, otherwise another
        // customer could rebind a PayPal Order they do not own to their own cart
        if ($paypalOrder->getIdCart() !== $cancelOrderRequest->getCartId()) {
            throw new PsCheckoutException(
                sprintf('PayPal Order %s is not linked to cart #%s', $cancelOrderRequest->getOrderId(), var_export($cancelOrderRequest->getCartId(), true)),
                PsCheckoutException::PAYPAL_ORDER_CART_MISMATCH
            );
        }

        try {
            // NOTE: the status is never taken from the request: a cancel request always means CANCELED
            if ($cancelOrderRequest->getFundingSource()) {
                $paypalOrder->setFundingSource($cancelOrderRequest->getFundingSource());
            }

            $paypalOrder
                ->setIsCardFields($cancelOrderRequest->isHostedFields())
                ->setIsExpressCheckout($cancelOrderRequest->isExpressCheckout())
                ->setStatus(PayPalOrderStatus::CANCELED);

            $this->payPalOrderRepository->save($paypalOrder);
        } catch (\Exception $exception) {
            throw new PsCheckoutException(sprintf('Unable to update PrestaShop Checkout session #%s', var_export($cancelOrderRequest->getCartId(), true)), PsCheckoutException::UPDATE_FAILED, $exception);
        }
    }
}
