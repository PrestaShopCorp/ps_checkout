<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Translations\OrderStatesTranslations;

class OrderStatesTranslationsTest extends TestCase
{
    /**
     * @dataProvider getLanguageText
     */
    public function testOrderStatesGetLanguageText($resultExpect, $dataToValidate)
    {
        $this->assertSame(
            $resultExpect,
            (new OrderStatesTranslations())->getTranslations($dataToValidate)
        );
    }

    public function getLanguageText()
    {
        return [
            [
                [
                    'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT' => OrderStatesTranslations::PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT['en'],
                    'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT' => OrderStatesTranslations::PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT['en'],
                    'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT' => OrderStatesTranslations::PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT['en'],
                    'PS_CHECKOUT_STATE_AUTHORIZED' => OrderStatesTranslations::PS_CHECKOUT_STATE_AUTHORIZED['en'],
                    'PS_CHECKOUT_STATE_PARTIAL_REFUND' => OrderStatesTranslations::PS_CHECKOUT_STATE_PARTIAL_REFUND['en'],
                    'PS_CHECKOUT_STATE_WAITING_CAPTURE' => OrderStatesTranslations::PS_CHECKOUT_STATE_WAITING_CAPTURE['en'],
                ],
                'en',
            ],
            [
                [
                    'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT' => OrderStatesTranslations::PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT['en'],
                    'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT' => OrderStatesTranslations::PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT['en'],
                    'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT' => OrderStatesTranslations::PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT['en'],
                    'PS_CHECKOUT_STATE_AUTHORIZED' => OrderStatesTranslations::PS_CHECKOUT_STATE_AUTHORIZED['en'],
                    'PS_CHECKOUT_STATE_PARTIAL_REFUND' => OrderStatesTranslations::PS_CHECKOUT_STATE_PARTIAL_REFUND['en'],
                    'PS_CHECKOUT_STATE_WAITING_CAPTURE' => OrderStatesTranslations::PS_CHECKOUT_STATE_WAITING_CAPTURE['en'],
                ],
                'notIsoCode',
            ],
        ];
    }
}
