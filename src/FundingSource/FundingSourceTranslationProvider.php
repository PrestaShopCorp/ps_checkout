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

namespace PrestaShop\Module\PrestashopCheckout\FundingSource;

class FundingSourceTranslationProvider
{
    private array $fundingSourceNames;

    private array $paymentOptionNames;

    public function __construct(\Ps_checkout $psCheckout)
    {
        $this->fundingSourceNames = [
            'card' => $psCheckout->l('Card', 'fundingsourcetranslationprovider'),
            'paypal' => 'PayPal',
            'venmo' => 'Venmo',
            'itau' => 'Itau',
            'credit' => 'PayPal Credit',
            'paylater' => 'PayPal Pay Later',
            'ideal' => 'iDEAL',
            'bancontact' => 'Bancontact',
            'giropay' => 'Giropay',
            'eps' => 'EPS',
            'sofort' => 'Sofort',
            'mybank' => 'MyBank',
            'blik' => 'BLIK',
            'p24' => 'Przelewy24',
            'zimpler' => 'Zimpler',
            'wechatpay' => 'WeChat Pay',
            'payu' => 'PayU',
            'verkkopankki' => 'Verkkopankki',
            'trustly' => 'Trustly',
            'oxxo' => 'OXXO',
            'boleto' => 'Boleto',
            'maxima' => 'Maxima',
            'mercadopago' => 'Mercado Pago',
            'sepa' => 'SEPA',
            'google_pay' => 'Google Pay',
            'apple_pay' => 'Apple Pay',
            'token' => $psCheckout->l('Pay with %s', 'fundingsourcetranslationprovider'),
        ];

        $payByTranslation = $psCheckout->l('Pay by %s', 'fundingsourcetranslationprovider');

        foreach ($this->fundingSourceNames as $fundingSource => $name) {
            switch ($fundingSource) {
                case 'paypal':
                    $this->paymentOptionNames[$fundingSource] = $psCheckout->l('Pay with a PayPal account', 'fundingsourcetranslationprovider');
                    break;
                case 'card':
                    $this->paymentOptionNames[$fundingSource] = $psCheckout->l('Pay by Card - 100% secure payments', 'fundingsourcetranslationprovider');
                    break;
                case 'paylater':
                    $this->paymentOptionNames[$fundingSource] = $psCheckout->l('Pay in installments with PayPal Pay Later', 'fundingsourcetranslationprovider');
                    break;
                default:
                    $this->paymentOptionNames[$fundingSource] = sprintf($payByTranslation, $name);
            }
        }

        // Provide a default wording "Pay by " for FO
        $this->paymentOptionNames['default'] = str_replace('%s', '', $payByTranslation);
    }

    /**
     * @param string $fundingSource
     *
     * @return string
     */
    public function getPaymentMethodName($fundingSource)
    {
        return $this->fundingSourceNames[$fundingSource] ?? '';
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    public function getVaultedPaymentMethodName($identifier)
    {
        return str_replace('%s', $identifier, $this->fundingSourceNames['token']);
    }

    /**
     * @return array
     */
    public function getPaymentOptionNames()
    {
        return $this->paymentOptionNames;
    }

    /**
     * @param string $fundingSource
     *
     * @return string
     */
    public function getPaymentOptionName($fundingSource)
    {
        return $this->paymentOptionNames[$fundingSource] ?? '';
    }
}
