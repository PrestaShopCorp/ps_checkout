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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\Builder;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\DTO\GooglePayDisplayItem;
use PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\DTO\GooglePayTransactionInfo;
use PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\DTO\MerchantInfo;
use PrestaShop\Module\PrestashopCheckout\Translations\Translations;

class GooglePayTransactionInfoBuilder
{
    /**
     * @var Translations
     */
    private $translations;

    public function __construct(Translations $translations)
    {
        $this->translations = current($translations->getTranslations())['google_pay'];
    }

    /**
     * @return GooglePayTransactionInfo
     *
     * @throws PsCheckoutException
     */
    public function buildFromPayPalPayload($payload)
    {
        $transactionInfo = new GooglePayTransactionInfo();

        $breakdown = $payload['amount']['breakdown'];

        $displayItems = [];

        if ($breakdown['shipping']['value'] > 0) {
            $shipping = new GooglePayDisplayItem();
            $shipping->setPrice($breakdown['shipping']['value'])
                ->setType(GooglePayDisplayItem::TYPE_LINE_ITEM)
                ->setLabel($this->translations['shipping']);
            $displayItems[] = $shipping;
        }

        if ($breakdown['handling']['value'] > 0) {
            $handling = new GooglePayDisplayItem();
            $handling->setPrice($breakdown['handling']['value'])
                ->setType(GooglePayDisplayItem::TYPE_LINE_ITEM)
                ->setLabel($this->translations['handling']);
            $displayItems[] = $handling;
        }

        if ($breakdown['discount']['value'] > 0) {
            $discount = new GooglePayDisplayItem();
            $discount->setPrice('-' . $breakdown['discount']['value'])
                ->setType(GooglePayDisplayItem::TYPE_LINE_ITEM)
                ->setLabel($this->translations['discount']);
            $displayItems[] = $discount;
        }

        $subtotal = new GooglePayDisplayItem();
        $subtotal->setPrice($this->formatAmount($payload['amount']['value'] - $breakdown['tax_total']['value'], $payload['amount']['currency_code']))
            ->setType(GooglePayDisplayItem::TYPE_SUBTOTAL)
            ->setLabel($this->translations['subtotal']);
        $displayItems[] = $subtotal;

        $tax = new GooglePayDisplayItem();
        $tax->setPrice($breakdown['tax_total']['value'])
            ->setType(GooglePayDisplayItem::TYPE_TAX)
            ->setLabel($this->translations['tax']);

        $displayItems[] = $tax;

        $productItems = array_map(function ($item) {
            $productItem = new GooglePayDisplayItem();
            $productItem->setPrice($this->formatAmount($item['unit_amount']['value'] * $item['quantity'], $item['unit_amount']['currency_code']))
                ->setType(GooglePayDisplayItem::TYPE_LINE_ITEM)
                ->setLabel($item['name'] . ' ' . $item['description'] . ' x' . $item['quantity']);

            return $productItem;
        }, $payload['items']);

        $displayItems = array_merge($productItems, $displayItems);

        $merchantInfo = new MerchantInfo();
        $merchantInfo->setMerchantName($payload['application_context']['brand_name']);

        $transactionInfo->setCurrencyCode($payload['amount']['currency_code'])
            ->setTotalPrice($payload['amount']['value'])
            ->setTotalPriceLabel($this->translations['total'])
            ->setDisplayItems($displayItems)
            ->setMerchantInfo($merchantInfo);

        return $transactionInfo;
    }

    /**
     * @return GooglePayTransactionInfo
     *
     * @throws PsCheckoutException
     */
    public function buildMinimalTransactionInfoFromPayPalPayload($payload)
    {
        $transactionInfo = new GooglePayTransactionInfo();

        $merchantInfo = new MerchantInfo();
        $merchantInfo->setMerchantName($payload['application_context']['brand_name']);

        $transactionInfo->setCurrencyCode($payload['amount']['currency_code'])
            ->setTotalPrice($payload['amount']['value'])
            ->setTotalPriceLabel($this->translations['total'])
            ->setMerchantInfo($merchantInfo);

        return $transactionInfo;
    }

    /**
     * Get decimal to round correspondent to the payment currency used
     * Advise from PayPal: Always round to 2 decimals except for HUF, JPY and TWD
     * currencies which require a round with 0 decimal
     *
     * @return int
     */
    private function getNbDecimalToRound($currencyIsoCode)
    {
        if (in_array($currencyIsoCode, ['HUF', 'JPY', 'TWD'], true)) {
            return 0;
        }

        return 2;
    }

    /**
     * @param float|int|string $amount
     *
     * @return string
     */
    private function formatAmount($amount, $currencyIsoCode)
    {
        return sprintf("%01.{$this->getNbDecimalToRound($currencyIsoCode)}F", $amount);
    }
}
