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
use PrestaShop\Module\PrestashopCheckout\Translations\Translations;

class PayPalOrderTranslationProvider
{
    /**
     * @var array
     */
    private $translations;

    /**
     * @var FundingSourceTranslationProvider
     */
    private $fundingSourceTranslationProvider;

    /**
     * @param Translations $translations
     * @param FundingSourceTranslationProvider $fundingSourceTranslationProvider
     */
    public function __construct(
        Translations $translations,
        FundingSourceTranslationProvider $fundingSourceTranslationProvider
    ) {
        $this->translations = current($translations->getTranslations());
        $this->fundingSourceTranslationProvider = $fundingSourceTranslationProvider;
    }

    /**
     * @param string $transactionStatus
     *
     * @return string
     */
    public function getTransactionStatusTranslated($transactionStatus)
    {
        return isset($this->translations['paypal']['capture']['status'][$transactionStatus])
            ? $this->translations['paypal']['capture']['status'][$transactionStatus]
            : '';
    }

    /**
     * @param string $orderStatus
     *
     * @return string
     */
    public function getOrderStatusTranslated($orderStatus)
    {
        return isset($this->translations['paypal']['order']['status'][$orderStatus])
            ? $this->translations['paypal']['order']['status'][$orderStatus]
            : '';
    }

    /**
     * @param string $fundingSource
     *
     * @return string
     */
    public function getFundingSourceTranslated($fundingSource)
    {
        return $this->fundingSourceTranslationProvider->getPaymentMethodName($fundingSource);
    }

    /**
     * @return array
     */
    public function getSummaryTranslations()
    {
        return $this->translations['order']['summary'];
    }
}
