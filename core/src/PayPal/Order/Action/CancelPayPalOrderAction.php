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
        try {
            $paypalOrder = $this->payPalOrderRepository->getOneBy(['id' => $cancelOrderRequest->getOrderId()]);

            if (!$paypalOrder) {
                return;
            }

            $paypalOrder
                ->setIdCart($cancelOrderRequest->getCartId())
                ->setPaymentTokenId($cancelOrderRequest->getOrderId()) // Assuming PayPal order ID is a payment token
                ->setFundingSource($cancelOrderRequest->getFundingSource())
                ->setIsCardFields($cancelOrderRequest->isHostedFields()) // Assuming "isHostedFields" maps to "isCardFields"
                ->setIsExpressCheckout($cancelOrderRequest->isExpressCheckout())
                ->setStatus($cancelOrderRequest->getOrderStatus());

            $this->payPalOrderRepository->save($paypalOrder);
        } catch (\Exception $exception) {
            throw new PsCheckoutException(sprintf('Unable to update PrestaShop Checkout session #%s', var_export($cancelOrderRequest->getCartId(), true)), PsCheckoutException::UPDATE_FAILED, $exception);
        }
    }
}
