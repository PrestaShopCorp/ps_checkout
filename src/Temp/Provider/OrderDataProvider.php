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

namespace PrestaShop\Module\PrestashopCheckout\Temp\Provider;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use PrestaShop\Module\PrestashopCheckout\PaypalCountryCodeMatrice;
use PrestaShop\Module\PrestashopCheckout\Temp\Adapter\OrderDataAdapter;

class OrderDataProvider
{
    /** @var array */
    private $orderData;

    /** @var OrderDataAdapter */
    private $orderDataAdapter;

    /**
     * @param array $orderData
     */
    public function __construct($orderData, $orderDataAdapter)
    {
        $this->orderData = $orderData;
        $this->orderDataAdapter = $orderDataAdapter;
    }

    /**
     * @return string
     */
    public function getBrandName()
    {
        return $this->orderData['shop']['name'];
    }

    /**
     * @return bool
     */
    public function isExpressCheckout()
    {
        return $this->orderData['psCheckout']['isExpressCheckout'];
    }

    /**
     * @return string
     */
    public function getShippingPreference()
    {
        return $this->isExpressCheckout() ? 'SET_PROVIDED_ADDRESS' : 'GET_FROM_FILE';
    }

    /**
     * @return string
     */
    public function getPayerGivenName()
    {
        return $this->orderData['payer']['given_name'];
    }

    /**
     * @return string
     */
    public function getPayerSurname()
    {
        return $this->orderData['payer']['surname'];
    }

    /**
     * @return false|string
     */
    public function getPayerCountryCode()
    {
        $paypalCountryCodeMatrice = new PaypalCountryCodeMatrice();
        $isoCode = strtoupper($this->orderDataAdapter->getIsoCountry($this->orderData['payer']['id_country']));

        return $paypalCountryCodeMatrice->getPaypalIsoCode($isoCode);
    }

    /**
     * @return string
     */
    public function getPayerAddressLine1()
    {
        return $this->orderData['payer']['address_line_1'];
    }

    /**
     * @return string
     */
    public function getPayerAddressLine2()
    {
        return $this->orderData['payer']['address_line_2'];
    }

    /**
     * @return string
     */
    public function getPayerAdminArea1()
    {
        return $this->orderDataAdapter->getStateName($this->orderData['payer']['id_state']);
    }

    /**
     * @return string
     */
    public function getPayerAdminArea2()
    {
        return $this->orderData['payer']['admin_area_2'];
    }

    /**
     * @return string
     */
    public function getPayerPostalCode()
    {
        return $this->orderData['payer']['postcode'];
    }

    /**
     * @return string
     */
    public function getPayerBirthdate()
    {
        return ($this->orderData['customer']['birthday'] !== '0000-00-00') ? $this->orderData['customer']['birthday'] : '';
    }

    /**
     * @return string
     */
    public function getPayerEmailAddress()
    {
        return $this->orderData['customer']['email_address'];
    }

    /**
     * @return string
     */
    public function getPayerId()
    {
        return $this->orderData['payer']['payer_id'];
    }

    /**
     * @return string
     */
    public function getPayerPhone()
    {
        $phone = !empty($this->orderData['payer']['phone']) ? $this->orderData['payer']['phone'] : '';

        return (empty($phone) && !empty($this->orderData['payer']['phone_mobile'])) ? $this->orderData['payer']['phone_mobile'] : $phone;
    }

    /**
     * @return string
     */
    public function getPayerPhoneType()
    {
        $utilPhoneType = '';
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $parsedPhone = $phoneUtil->parse($this->getPayerPhone(), $this->getPayerCountryCode());
            if ($phoneUtil->isValidNumber($parsedPhone)) {
                $utilPhoneType = $phoneUtil->getNumberType($parsedPhone);
            }

            switch ($utilPhoneType) {
                case PhoneNumberType::MOBILE:
                    return 'MOBILE';
                case PhoneNumberType::PAGER:
                    return 'PAGER';
                default:
                    return 'OTHER';
            }
        } catch (NumberParseException $exception) {
            $module = \Module::getInstanceByName('ps_checkout');
            $module->getLogger()->warning(
                'Unable to format phone number on PayPal Order payload',
                [
                    'phone' => $this->getPayerPhone(),
                    'exception' => $exception,
                ]
            );
        }

        return '';
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->orderData['currency']['iso_code'];
    }

    /**
     * @return float
     */
    public function getCartTotalAmount()
    {
        return $this->orderData['cart']['total_with_taxes'];
    }

    /**
     * @return int
     */
    public function getPurchaseUnitCustomId()
    {
        return $this->orderData['cart']['id'];
    }

    /**
     * @return string
     */
    public function getPurchaseUnitDescription()
    {
        return mb_substr(
            'Checking out with your cart #' . $this->getPurchaseUnitCustomId() . ' from ' . $this->getBrandName(),
            0,
            127
        );
    }

    /**
     * @TODO
     *
     * @return string
     */
    public function getPurchaseUnitInvoiceId()
    {
        return '';
    }

    /**
     * @TODO
     *
     * @return string
     */
    public function getPurchaseUnitReferenceId()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getPurchaseUnitSoftDescriptor()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getPayeeEmailAddress()
    {
        return $this->orderData['payee']['email_address'];
    }

    /**
     * @return string
     */
    public function getPayeeMerchantId()
    {
        return $this->orderData['payee']['merchant_id'];
    }

    /**
     * @return array|false|mixed[]|null
     */
    public function getCartItems()
    {
        return $this->orderData['cart']['items'];
    }

    /**
     * @return string
     */
    public function getShippingCountryCode()
    {
        $paypalCountryCodeMatrice = new PaypalCountryCodeMatrice();
        $isoCode = strtoupper($this->orderDataAdapter->getIsoCountry($this->orderData['shipping']['id_country']));

        return $paypalCountryCodeMatrice->getPaypalIsoCode($isoCode);
    }

    /**
     * @return string
     */
    public function getShippingAddressLine1()
    {
        return $this->orderData['shipping']['address_line_1'];
    }

    /**
     * @return string
     */
    public function getShippingAddressLine2()
    {
        return $this->orderData['shipping']['address_line_2'];
    }

    /**
     * @return string
     */
    public function getShippingAdminArea1()
    {
        return $this->orderDataAdapter->getStateName($this->orderData['shipping']['id_state']);
    }

    /**
     * @return string
     */
    public function getShippingAdminArea2()
    {
        return $this->orderData['shipping']['admin_area_2'];
    }

    /**
     * @return string
     */
    public function getShippingPostalCode()
    {
        return $this->orderData['shipping']['postcode'];
    }

    /**
     * @return string
     */
    public function getShippingSurname()
    {
        return $this->orderData['shipping']['surname'];
    }

    /**
     * @return string
     */
    public function getShippingGivenName()
    {
        return $this->orderData['shipping']['given_name'];
    }

    /**
     * @return string
     */
    public function getShippingFullName()
    {
        $genderName = $this->orderDataAdapter->getGenderName($this->orderData['customer']['id_gender'], $this->orderData['cart']['id_lang']);

        return $genderName . ' ' . $this->getShippingSurname() . ' ' . $this->getShippingGivenName();
    }

    /**
     * @TODO
     *
     * @return string
     */
    public function getShippingType()
    {
        return true ? 'SHIPPING' : 'PICKUP_IN_PERSON';
    }

    /**
     * @return string
     */
    public function getShippingCost()
    {
        return $this->orderData['cart']['shipping_cost'];
    }

    /**
     * @return float
     */
    public function getGiftWrappingAmount()
    {
        return $this->orderData['cart']['subtotals']['gift_wrapping']['amount'];
    }
}
