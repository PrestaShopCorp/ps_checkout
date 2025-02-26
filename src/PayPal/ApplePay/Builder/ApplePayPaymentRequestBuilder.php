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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\ApplePay\Builder;

use PrestaShop\Module\PrestashopCheckout\PayPal\ApplePay\DTO\ApplePayLineItem;
use PrestaShop\Module\PrestashopCheckout\PayPal\ApplePay\DTO\ApplePayPaymentRequest;
use PrestaShop\Module\PrestashopCheckout\Translations\Translations;

class ApplePayPaymentRequestBuilder
{
    /**
     * @var Translations
     */
    private $translations;

    public function __construct(Translations $translations)
    {
        $this->translations = current($translations->getTranslations())['apple_pay'];
    }

    /**
     * @return ApplePayPaymentRequest
     */
    public function buildMinimalPaymentRequestFromPayPalPayload($payload)
    {
        $paymentRequest = new ApplePayPaymentRequest();

        $total = new ApplePayLineItem();
        $total->setAmount($payload['amount']['value'])
            ->setLabel($this->translations['total']);

        $paymentRequest->setCurrencyCode($payload['amount']['currency_code'])
            ->setTotal($total);

        return $paymentRequest;
    }
}
