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

use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceTranslationProvider;
use PrestaShop\Module\PrestashopCheckout\PsCheckoutDataProvider;

class PayPalOrderPresenter
{
    /**
     * @var PaypalOrderDataProvider
     */
    public $paypalOrderDataProvider;

    /**
     * @var PsCheckoutDataProvider
     */
    public $psCheckoutDataProvider;

    /**
     * @var FundingSourceTranslationProvider
     */
    public $fundingSourceTranslationProvider;

    /**
     * @var PayPalOrderTranslationProvider
     */
    public $paypalOrderTranslationProvider;

    /**
     * @param PaypalOrderDataProvider $paypalOrderDataProvider
     * @param PsCheckoutDataProvider $psCheckoutDataProvider
     * @param FundingSourceTranslationProvider $fundingSourceTranslationProvider
     * @param PayPalOrderTranslationProvider $paypalOrderTranslationProvider
     */
    public function __construct(PaypalOrderDataProvider $paypalOrderDataProvider, PsCheckoutDataProvider $psCheckoutDataProvider, FundingSourceTranslationProvider $fundingSourceTranslationProvider, PayPalOrderTranslationProvider $paypalOrderTranslationProvider)
    {
        $this->paypalOrderDataProvider = $paypalOrderDataProvider;
        $this->psCheckoutDataProvider = $psCheckoutDataProvider;
        $this->fundingSourceTranslationProvider = $fundingSourceTranslationProvider;
        $this->paypalOrderTranslationProvider = $paypalOrderTranslationProvider;
    }

    /**
     * @param string $orderStatus
     * 
     * @return string
     */
    public function getOrderStatus($orderStatus)
    {
        return $this->paypalOrderTranslationProvider->getTranslatedOrderStatus($orderStatus);
    }

    /**
     * @param string $transactionStatus
     * 
     * @return string
     */
    public function getTransactionStatus($transactionStatus)
    {
        return $this->paypalOrderTranslationProvider->getTranslatedTransactionStatus($transactionStatus);
    }

    /**
     * @param string $fundingSource
     * 
     * @return string
     */
    public function getFundingSourceName($fundingSource)
    {
        return $this->fundingSourceTranslationProvider->getPaymentMethodName($fundingSource);
    }
}