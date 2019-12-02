<?php
/**
 * 2007-2019 PrestaShop and Contributors
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
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout\PsxData;

/**
 * Check and set the merchant status
 */
class PsxDataValidation
{
    const NOT_CORRECT = 'is not correct';
    const DATA_ERROR = 'Data can\'t be empty';
    const FIRST_NAME = 'First name ' . self::NOT_CORRECT;
    const LAST_NAME = 'Last name ' . self::NOT_CORRECT;
    const LANGUAGE = 'Language ' . self::NOT_CORRECT;
    const QUALIFICATION = 'Peronnal information ' . self::NOT_CORRECT;
    const STREET = 'Street name ' . self::NOT_CORRECT;
    const CITY = 'City name ' . self::NOT_CORRECT;
    const COUNTRY = 'Country name ' . self::NOT_CORRECT;
    const STATE = 'State ' . self::NOT_CORRECT;
    const ZIPCODE = 'Zip code ' . self::NOT_CORRECT;
    const TYPE = 'Type ' . self::NOT_CORRECT;
    const PHONE_COUNTRY = 'Phone country code ' . self::NOT_CORRECT;
    const PHONE = 'Phone ' . self::NOT_CORRECT;
    const WEBSITE = 'Website ' . self::NOT_CORRECT;
    const GENDER = 'Gender ' . self::NOT_CORRECT;
    const SHOP_NAME = 'Shop name ' . self::NOT_CORRECT;
    const COMPANY_EMR = 'Company monthly average ' . self::NOT_CORRECT;
    const CATEGORY = 'Company category ' . self::NOT_CORRECT;
    const SUB_CATEGORY = 'Company sub-category ' . self::NOT_CORRECT;

    /**
     * Validate Maasland Data
     *
     * @param array $data
     *
     * @return array
     */
    public function validateData($data)
    {
        $errors = [];

        if (empty($data)) {
            $errors[] = self::DATA_ERROR;

            return $errors;
        }

        if (strlen($data['business_contact_first_name']) < 1 || strlen($data['business_contact_first_name']) > 127) {
            $errors[] = self::FIRST_NAME;
        }

        if (strlen($data['business_contact_last_name']) < 1 || strlen($data['business_contact_last_name']) > 127) {
            $errors[] = self::LAST_NAME;
        }

        if (strlen($data['business_contact_language']) < 1 || strlen($data['business_contact_language']) > 4) {
            $errors[] = self::LANGUAGE;
        }

        /* Is no mandatory */
        if (!empty($data['qualification']) && strlen($data['qualification']) > 127) {
            $errors[] = self::QUALIFICATION;
        }

        if (strlen($data['business_address_street']) < 1 || strlen($data['business_address_street']) > 255) {
            $errors[] = self::STREET;
        }

        if (strlen($data['business_address_city']) < 1 || strlen($data['business_address_city']) > 127) {
            $errors[] = self::CITY;
        }

        if (strlen($data['business_address_country']) < 1 || strlen($data['business_address_country']) > 4) {
            $errors[] = self::COUNTRY;
        }

        if (in_array(
            $data['business_address_country'],
            ['AR', 'BR', 'CA', 'CN', 'IN', 'ID', 'IT', 'JP', 'MX', 'TH', 'US'])
            && strlen($data['business_address_state']) < 1
        ) {
            $errors[] = self::STATE;
        }

        if (strlen($data['business_address_zip']) < 1 || strlen($data['business_address_zip']) > 31) {
            $errors[] = self::ZIPCODE;
        }

        if (!preg_match('/^[0-9]{1,3}?$/', $data['business_phone_country'])
            || strlen($data['business_phone_country']) < 1
            || strlen($data['business_phone_country']) > 3
        ) {
            $errors[] = self::PHONE_COUNTRY;
        }

        if (!preg_match('/^^[0-9]{1,14}$/', $data['business_phone'])
            || strlen($data['business_phone']) < 1
            || strlen($data['business_phone']) > 14
        ) {
            $errors[] = self::PHONE;
        }

        if (!empty($data['business_website'])
            && (!preg_match('/^(http(s)?:\/\/)[\w.-]+(?:\.[\w\.-]+)+[\w\d?(\/)]$/m', $data['business_website']) || strlen($data['business_website']) > 255)
        ) {
            $errors[] = self::WEBSITE;
        }

        if (!in_array(
            $data['business_contact_gender'],
            ['Mr', 'Ms']
        )) {
            $errors[] = self::GENDER;
        }

        if (strlen($data['shop_name']) < 1 || strlen($data['shop_name']) > 255) {
            $errors[] = self::SHOP_NAME;
        }

        if (!in_array(
            $data['business_company_emr'],
            ['lt5000', 'lt25000', 'lt50000', 'lt100000', 'lt250000', 'lt500000', 'lt1000000', 'gt1000000']
        )) {
            $errors[] = self::COMPANY_EMR;
        }

        if ($data['business_category'] < 1000 || $data['business_category'] > 1025) {
            $errors[] = self::CATEGORY;
        }

        if (!empty($data['business_sub_category'])
            && ($data['business_sub_category'] < 2000 || $data['business_sub_category'] > 2297)
        ) {
            $errors[] = self::SUB_CATEGORY;
        }

        return $errors;
    }
}
