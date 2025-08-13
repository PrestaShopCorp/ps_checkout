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

namespace PsCheckout\Presentation\Presenter\FundingSource;

use PsCheckout\Presentation\TranslatorInterface;

class FundingSourceTranslationProvider implements FundingSourceTranslationProviderInterface
{
    /**
     * @var array
     */
    private $fundingSourceNames;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->initializeFundingSourceNames();
    }

    /**
     * {@inheritdoc}
     */
    public function getFundingSourceName(string $fundingSourceName): string
    {
        return $this->fundingSourceNames[$fundingSourceName] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethodName(string $fundingSourceName, string $fundingSourceLabel): string
    {
        $payByTranslation = $this->translator->trans('Pay by %s');

        switch ($fundingSourceName) {
            case 'card':
                return $this->translator->trans('Pay by Card - 100% secure payments');
            case 'paypal':
                return $this->translator->trans('Pay with a PayPal account');
            case 'paylater':
                return $this->translator->trans('Pay in installments with PayPal Pay Later');
            default:
                return sprintf($payByTranslation, $fundingSourceLabel);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getVaultedPaymentMethodName(string $identifier): string
    {
        return str_replace('%s', $identifier, $this->fundingSourceNames['token']);
    }

    /**
     * Initializes funding source names.
     */
    private function initializeFundingSourceNames()
    {
        $this->fundingSourceNames = [
            'card' => $this->translator->trans('Card'),
            'paypal' => 'PayPal',
            'venmo' => 'Venmo',
            'itau' => 'Itau',
            'credit' => 'PayPal Credit',
            'paylater' => 'PayPal Pay Later',
            'ideal' => 'iDEAL',
            'bancontact' => 'Bancontact',
            'eps' => 'EPS',
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
            'token' => $this->translator->trans('Pay with %s'),
        ];
    }
}
