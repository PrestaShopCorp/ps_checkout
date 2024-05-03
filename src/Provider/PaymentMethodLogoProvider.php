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

namespace PrestaShop\Module\PrestashopCheckout\Provider;

class PaymentMethodLogoProvider
{
    /**
     * @var string
     */
    private $modulePathUri;

    public function __construct($modulePathUri)
    {
        $this->modulePathUri = $modulePathUri;
    }

    /**
     * @param array $paymentSource
     *
     * @return string
     */
    public function getLogoByPaymentSource($paymentSource)
    {
        $paymentSourceName = key($paymentSource);

        if ($paymentSourceName === 'card' && isset($paymentSource['card']['brand'])) {
            switch ($paymentSource['card']['brand']) {
                case 'CB_NATIONALE':
                    return $this->modulePathUri . 'views/img/cb.svg';
                case 'VISA':
                    return $this->modulePathUri . 'views/img/visa.svg';
                case 'MASTERCARD':
                    return $this->modulePathUri . 'views/img/mastercard.svg';
                case 'AMEX':
                    return $this->modulePathUri . 'views/img/amex.svg';
                case 'DISCOVER':
                    return $this->modulePathUri . 'views/img/discover.svg';
                case 'JCB':
                    return $this->modulePathUri . 'views/img/jcb.svg';
                case 'DINERS':
                    return $this->modulePathUri . 'views/img/diners.svg';
                case 'UNIONPAY':
                    return $this->modulePathUri . 'views/img/unionpay.svg';
                case 'MAESTRO':
                    return $this->modulePathUri . 'views/img/maestro.svg';
            }
        }

        return $this->modulePathUri . 'views/img/' . $paymentSourceName . '.svg';
    }
}
