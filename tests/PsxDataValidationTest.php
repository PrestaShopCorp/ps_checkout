<?php
/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2019 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 **/
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PsxData\PsxDataValidation;

class PsxDataValidationTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testData($resultExpect, $dataToValidate)
    {
        $this->assertSame(
            $resultExpect,
            (new PsxDataValidation())->validateData($dataToValidate)
        );
    }

    public function dataProvider()
    {
        return array(
            array(
                array(
                    PsxDataValidation::DATA_ERROR,
                ),
                array(),
            ),
            array(
                array(
                    PsxDataValidation::FIRST_NAME,
                    PsxDataValidation::LAST_NAME,
                    PsxDataValidation::LANGUAGE,
                    PsxDataValidation::STREET,
                    PsxDataValidation::CITY,
                    PsxDataValidation::COUNTRY,
                    PsxDataValidation::ZIPCODE,
                    PsxDataValidation::PHONE_COUNTRY,
                    PsxDataValidation::PHONE,
                    PsxDataValidation::WEBSITE,
                    PsxDataValidation::GENDER,
                    PsxDataValidation::SHOP_NAME,
                    PsxDataValidation::COMPANY_EMR,
                    PsxDataValidation::CATEGORY,
                    PsxDataValidation::SUB_CATEGORY,
                ),
                array(
                    'business_contact_first_name' => '',
                    'business_contact_last_name' => '',
                    'business_contact_language' => '',
                    'business_address_street' => '',
                    'business_address_city' => '',
                    'business_address_country' => '',
                    'business_address_zip' => '',
                    'business_phone_country' => '',
                    'business_phone' => '',
                    'business_website' => ' ',
                    'business_contact_gender' => '',
                    'shop_name' => '',
                    'business_company_emr' => ' ',
                    'business_category' => '',
                    'business_sub_category' => ' ',
                ),
            ),
            array(
                array(
                    PsxDataValidation::FIRST_NAME,
                ),
                array(
                    'business_contact_first_name' => '2ckKzzAH5F68FWWKQ9wieF1dW85bgTCQuer6OEpMtrJCTOLOnDk8gOESC7TjximRhZkOTPTWLL6Va0AwX3eTOSL8HJduJtsuH3qnxuq9Kedbl8xBt9rxm87v5x63GD6f',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::LAST_NAME,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => '2ckKzzAH5F68FWWKQ9wieF1dW85bgTCQuer6OEpMtrJCTOLOnDk8gOESC7TjximRhZkOTPTWLL6Va0AwX3eTOSL8HJduJtsuH3qnxuq9Kedbl8xBt9rxm87v5x63GD6f',
                    'business_contact_language' => 'US',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::LANGUAGE,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'FR FROM FR',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::QUALIFICATION,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'qualification' => '11Mz8IjBZFNUvqNHTBfBbq4Y1VXjOQS0O8kZ02yK6oeDLXQ47fXanLiTm9V6WANBXtCi6L7zgvrcPB8jHyZ8bp5CA197vMHccBFnFYeDpKVTrstv3X2XNwMCPZaTH1z0M4Euv3VC1eNVNIoyzWCFbbcQESvxQag32l4MjfqCSbSQ34N3PaxwxTeSAaSmuENiMHhHxdIPWFkONi0k8oWZSHDqAvwHNQi9D2G4IfXYRcXPBS4zZqwfl0Ft1G6oSukN',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::STREET,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'business_address_street' => '11Mz8IjBZFNUvqNHTBfBbq4Y1VXjOQS0O8kZ02yK6oeDLXQ47fXanLiTm9V6WANBXtCi6L7zgvrcPB8jHyZ8bp5CA197vMHccBFnFYeDpKVTrstv3X2XNwMCPZaTH1z0M4Euv3VC1eNVNIoyzWCFbbcQESvxQag32l4MjfqCSbSQ34N3PaxwxTeSAaSmuENiMHhHxdIPWFkONi0k8oWZSHDqAvwHNQi9D2G4IfXYRcXPBS4zZqwfl0Ft1G6oSukN',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::CITY,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => '2ckKzzAH5F68FWWKQ9wieF1dW85bgTCQuer6OEpMtrJCTOLOnDk8gOESC7TjximRhZkOTPTWLL6Va0AwX3eTOSL8HJduJtsuH3qnxuq9Kedbl8xBt9rxm87v5x63GD6f',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::COUNTRY,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'frfrr',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::ZIPCODE,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => 'iuId2fV7IByE6z5rLDYRewV9nSWTVWxw',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::PHONE_COUNTRY,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '1000',
                    'business_phone' => '84-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::PHONE,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '+eeee 84-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::WEBSITE,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'www:// @testshop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::GENDER,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'a gender',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::SHOP_NAME,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'LsH9e29RMyNOgQXK87eO3o0fShaP3TPbk85yqqD7gVJPaNedsXiPebZVWPmwVSz9G8JEq5hLZYRGoDADW0fPEu07LfxXy33kWSk97SjwbczKWRZlXm6yWKY39P7gKx6VdywuEXXoGSxgRLkFIImDYtdLW2XJ9z5eslORE7zvVK1nO4IuH33JENUvCP3kdtA4jb0gWZNCKqHZpoYri3920NStnfKr7viDo4jUOcTjeTJJ5jLN6gYuAlqMh0yNlnos',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::COMPANY_EMR,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt50000000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::CATEGORY,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '999',
                    'business_sub_category' => '2001',
                ),
            ),
            array(
                array(
                    PsxDataValidation::SUB_CATEGORY,
                ),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '1999',
                ),
            ),
            array(
                array(),
                array(
                    'business_contact_first_name' => 'Sue',
                    'business_contact_last_name' => 'Pachooz',
                    'business_contact_language' => 'US',
                    'business_address_street' => '42 rue des petits souliers',
                    'business_address_city' => 'Lutece',
                    'business_address_country' => 'FR',
                    'business_address_zip' => '123456',
                    'business_phone_country' => '33',
                    'business_phone' => '7784-5479-1234',
                    'business_website' => 'https://shop.my-shoes.com',
                    'business_contact_gender' => 'Ms',
                    'shop_name' => 'My shoe shop',
                    'business_company_emr' => 'lt5000',
                    'business_category' => '1001',
                    'business_sub_category' => '2001',
                ),
            ),
        );
    }
}
