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
use PrestaShop\Module\PrestashopCheckout\ShopContext;

class PayPalOrderSummaryView
{
    /**
     * @var PaypalOrderDataProvider
     */
    private $orderPayPalDataProvider;

    /**
     * @var OrderDataProvider
     */
    private $orderDataProvider;

    /**
     * @var PsCheckoutDataProvider
     */
    private $checkoutDataProvider;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var PayPalOrderPresenter
     */
    private $orderPayPalPresenter;

    /**
     * @var ShopContext
     */
    private $shopContext;

    /**
     * @param PaypalOrderDataProvider $orderPayPalDataProvider
     * @param OrderDataProvider $orderDataProvider
     * @param PsCheckoutDataProvider $checkoutDataProvider
     * @param Router $router
     * @param PayPalOrderPresenter $orderPayPalPresenter
     * @param ShopContext $shopContext
     */
    public function __construct(
        PaypalOrderDataProvider $orderPayPalDataProvider,
        OrderDataProvider $orderDataProvider,
        PsCheckoutDataProvider $checkoutDataProvider,
        Router $router,
        PayPalOrderPresenter $orderPayPalPresenter,
        ShopContext $shopContext
    ) {
        $this->orderPayPalDataProvider = $orderPayPalDataProvider;
        $this->orderDataProvider = $orderDataProvider;
        $this->checkoutDataProvider = $checkoutDataProvider;
        $this->router = $router;
        $this->orderPayPalPresenter = $orderPayPalPresenter;
        $this->shopContext = $shopContext;
    }

    /**
     * Returns an array of template variables for smarty
     *
     * @return array
     */
    public function getTemplateVars()
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
            'isShop17' => $this->shopContext->isShop17(),
            'translations' => $this->orderPayPalPresenter->getSummaryTranslations(),
            'vault' => $this->orderPayPalDataProvider->isIntentToVault(),
            'tokenIdentifier' => $this->orderPayPalDataProvider->getPaymentTokenIdentifier(),
            'isTokenSaved' => $this->orderPayPalDataProvider->isTokenSaved(),
        ];
    }
}
