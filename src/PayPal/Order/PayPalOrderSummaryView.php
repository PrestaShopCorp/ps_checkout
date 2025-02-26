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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order;

use PrestaShop\Module\PrestashopCheckout\Order\OrderDataProvider;
use PrestaShop\Module\PrestashopCheckout\PsCheckoutDataProvider;
use PrestaShop\Module\PrestashopCheckout\Routing\Router;

class PayPalOrderSummaryView
{
    public function __construct(
        private PaypalOrderDataProvider $orderPayPalDataProvider,
        private OrderDataProvider $orderDataProvider,
        private PsCheckoutDataProvider $checkoutDataProvider,
        private Router $router,
        private PayPalOrderPresenter $orderPayPalPresenter,
    ) {
    }

    /**
     * Returns an array of template variables for smarty
     */
    public function getTemplateVars(): array
    {
        $orderStatus = $this->orderPayPalDataProvider->getOrderStatus() ? $this->orderPayPalDataProvider->getOrderStatus() : $this->checkoutDataProvider->getPaypalOrderStatus();
        $orderTransactionStatus = $this->orderPayPalDataProvider->getTransactionStatus();
        $fundingSource = $this->checkoutDataProvider->getFundingSourceName();

        return [
            'orderIsPaid' => $this->orderDataProvider->hasBeenPaid(),
            'orderPayPalId' => $this->checkoutDataProvider->getPaypalOrderId(),
            'orderPayPalStatus' => $orderStatus,
            'orderPayPalStatusTranslated' => $this->orderPayPalPresenter->getOrderStatusTranslated($orderStatus),
            'orderPayPalFundingSource' => $fundingSource,
            'orderPayPalFundingSourceTranslated' => $this->orderPayPalPresenter->getFundingSourceTranslated($fundingSource),
            'orderPayPalTransactionId' => $this->orderPayPalDataProvider->getTransactionId(),
            'orderPayPalTransactionStatus' => $orderTransactionStatus,
            'orderPayPalTransactionStatusTranslated' => $this->orderPayPalPresenter->getTransactionStatusTranslated($orderTransactionStatus),
            'orderPayPalTransactionAmount' => $this->orderPayPalPresenter->getTotalAmountFormatted(),
            'approvalLink' => $this->orderPayPalDataProvider->getApprovalLink(),
            'payerActionLink' => $this->orderPayPalDataProvider->getPayActionLink(),
            'contactUsLink' => $this->router->getContactLink($this->orderDataProvider->getOrderId()),
            'translations' => $this->orderPayPalPresenter->getSummaryTranslations(),
            'vault' => $this->orderPayPalDataProvider->isIntentToVault(),
            'tokenIdentifier' => $this->orderPayPalDataProvider->getPaymentTokenIdentifier(),
            'isTokenSaved' => $this->orderPayPalDataProvider->isTokenSaved(),
        ];
    }
}
