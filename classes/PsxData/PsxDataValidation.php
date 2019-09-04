<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
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
    const NATIONNALITY = 'Nationnality ' . self::NOT_CORRECT;
    const STREET = 'Street name ' . self::NOT_CORRECT;
    const CITY = 'City name ' . self::NOT_CORRECT;
    const COUNTRY = 'Country name ' . self::NOT_CORRECT;
    const ZIPCODE = 'Zip code ' . self::NOT_CORRECT;
    const TYPE = 'Type ' . self::NOT_CORRECT;
    const PHONE_COUNTRY = 'Phone country code ' . self::NOT_CORRECT;
    const PHONE = 'Phone ' . self::NOT_CORRECT;
    const WEBSITE = 'Website ' . self::NOT_CORRECT;
    const GENDER = 'Gender ' . self::NOT_CORRECT;
    const SHOP_NAME = 'Shop name ' . self::NOT_CORRECT;
    const COMPANY_SIZE = 'Company size ' . self::NOT_CORRECT;
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
        $errors = array();

        $businessTypeValuesNeeded = array(
            'INDIVIDUAL',
            'PROPRIETORSHIP',
            'NONPROFIT',
            'GOVERNMENT',
            'GENERAL_PARTNERSHIP',
            'LIMITED_PARTNERSHIP',
            'LIMITED_LIABILITY_PARTNERSHIP',
            'LIMITED_LIABILITY_PROPRIETORS',
            'PRIVATE_CORPORATION',
            'PUBLIC_CORPORATION',
        );

        if (empty($data)) {
            return $errors[] = self::DATA_ERROR;
        }

        if (strlen($data['business_contact_first_name']) < 1 || strlen($data['business_contact_first_name']) > 127) {
            $errors[] = self::FIRST_NAME;
        }

        if (strlen($data['business_contact_last_name']) < 1 || strlen($data['business_contact_last_name']) > 127) {
            $errors[] = self::LAST_NAME;
        }

        if (strlen($data['business_contact_nationality']) < 1 || strlen($data['business_contact_nationality']) > 4) {
            $errors[] = self::NATIONNALITY;
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

        if (strlen($data['business_address_zip']) < 1 || strlen($data['business_address_zip']) > 31) {
            $errors[] = self::ZIPCODE;
        }

        if (!in_array($data['business_type'], $businessTypeValuesNeeded)) {
            $errors[] = self::TYPE;
        }

        if (!preg_match('/^[0-9]{1,3}?$/', $data['business_phone_country'])
            || strlen($data['business_phone_country']) < 1
            || strlen($data['business_phone_country']) > 3
        ) {
            $errors[] = self::PHONE_COUNTRY;
        }

        if (!preg_match('/^[0-9\-\(\)\/\+\s]*$/', $data['business_phone'])
            || strlen($data['business_phone']) < 1
            || strlen($data['business_phone']) > 63
        ) {
            $errors[] = self::PHONE;
        }

        if (!filter_var($data['business_website'], FILTER_VALIDATE_URL)
            || strlen($data['business_website']) < 1
            || strlen($data['business_website']) > 255
        ) {
            $errors[] = self::WEBSITE;
        }

        if (strlen($data['business_contact_gender']) < 1 || strlen($data['business_contact_gender']) > 7) {
            $errors[] = self::GENDER;
        }

        if (strlen($data['shop_name']) < 1 || strlen($data['shop_name']) > 255) {
            $errors[] = self::SHOP_NAME;
        }

        if (!in_array($data['business_company_size'], array('lt5', 'lt19', 'lt99', 'lt500', 'gt500'))) {
            $errors[] = self::COMPANY_SIZE;
        }

        if ($data['business_category'] < 1000 || $data['business_category'] > 1025) {
            $errors[] = self::CATEGORY;
        }

        if ($data['business_sub_category'] < 2000 || $data['business_sub_category'] > 2297) {
            $errors[] = self::SUB_CATEGORY;
        }

        return $errors;
    }
}
