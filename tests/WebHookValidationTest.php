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
use PrestaShop\Module\PrestashopCheckout\WebHookValidation;

class WebHookValidationTest extends TestCase
{
    /**
     * @dataProvider headerDataProvider
     */
    public function testHeaderDatas($resultExpect, $dataToValidate)
    {
        $this->assertSame(
            $resultExpect,
            (new WebHookValidation())->validateHeaderDatas($dataToValidate)
        );
    }

    public function headerDataProvider()
    {
        return array(
            array(
                array(
                    WebHookValidation::HEADER_DATA_ERROR,
                ),
                array(),
            ),
            array(
                array(
                    WebHookValidation::HEADER_SHOPID_ERROR,
                    WebHookValidation::HEADER_MERCHANTID_ERROR,
                    WebHookValidation::HEADER_PSXID_ERROR,
                ),
                array(
                    'Shop-Id' => '',
                    'Merchant-Id' => '',
                    'Psx-Id' => '',
                ),
            ),
            array(
                array(
                    WebHookValidation::HEADER_SHOPID_ERROR,
                ),
                array(
                    'Shop-Id' => '',
                    'Merchant-Id' => 'SZKJZHXXXXXXX',
                    'Psx-Id' => 'wVH5CmKq4XeJkXXXXXXXXXXXXXXX',
                ),
            ),
            array(
                array(
                    WebHookValidation::HEADER_MERCHANTID_ERROR,
                ),
                array(
                    'Shop-Id' => '9a053ac6-9e7c-4c75-b57d-XXXXXXXX',
                    'Merchant-Id' => '',
                    'Psx-Id' => 'wVH5CmKq4XeJkXXXXXXXXXXXXXXX',
                ),
            ),
            array(
                array(
                    WebHookValidation::HEADER_PSXID_ERROR,
                ),
                array(
                    'Shop-Id' => '9a053ac6-9e7c-4c75-b57d-XXXXXXXX',
                    'Merchant-Id' => 'SZKJZHXXXXXXX',
                    'Psx-Id' => '',
                ),
            ),
            array(
                array(
                    WebHookValidation::HEADER_PSXID_ERROR,
                ),
                array(
                    'Shop-Id' => '9a053ac6-9e7c-4c75-b57d-XXXXXXXX',
                    'Merchant-Id' => 'SZKJZHXXXXXXX',
                ),
            ),
            array(
                array(),
                array(
                    'Shop-Id' => '9a053ac6-9e7c-4c75-b57d-XXXXXXXX',
                    'Merchant-Id' => 'SZKJZHXXXXXXX',
                    'Psx-Id' => 'wVH5CmKq4XeJkXXXXXXXXXXXXXXX',
                ),
            ),
        );
    }

    /**
     * @dataProvider bodyDataProvider
     */
    public function testBodyDatas($resultExpect, $dataToValidate)
    {
        $this->assertSame(
            $resultExpect,
            (new WebHookValidation())->validateBodyDatas($dataToValidate)
        );
    }

    public function bodyDataProvider()
    {
        return array(
            array(
                array(
                    WebHookValidation::BODY_DATA_ERROR,
                ),
                array(),
            ),
            array(
                array(
                    WebHookValidation::BODY_EVENTTYPE_ERROR,
                    WebHookValidation::BODY_CATEGORY_ERROR,
                    WebHookValidation::BODY_RESOURCE_ERROR,
                ),
                array(
                    'eventType' => '',
                    'category' => '',
                    'resource' => '',
                ),
            ),
            array(
                array(
                    WebHookValidation::BODY_EVENTTYPE_ERROR,
                ),
                array(
                    'eventType' => '',
                    'category' => 'ShopNotificationMerchantAccount',
                    'resource' => array(
                        'amount',
                    ),
                ),
            ),
            array(
                array(
                    WebHookValidation::BODY_CATEGORY_ERROR,
                ),
                array(
                    'eventType' => 'PaymentCaptureRefunded',
                    'category' => '',
                    'resource' => array(
                        'amount',
                    ),
                ),
            ),
            array(
                array(
                    WebHookValidation::BODY_RESOURCE_ERROR,
                ),
                array(
                    'eventType' => 'PaymentCaptureRefunded',
                    'category' => 'ShopNotificationMerchantAccount',
                    'resource' => array(),
                ),
            ),
            array(
                array(
                    WebHookValidation::BODY_RESOURCE_ERROR,
                ),
                array(
                    'eventType' => 'PaymentCaptureRefunded',
                    'category' => 'ShopNotificationMerchantAccount',
                ),
            ),
            array(
                array(),
                array(
                    'eventType' => 'PaymentCaptureRefunded',
                    'category' => 'ShopNotificationMerchantAccount',
                    'resource' => array(
                        'amount',
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider resourceDataProvider
     */
    public function testResourceDatas($resultExpect, $dataToValidate)
    {
        $this->assertSame(
            $resultExpect,
            (new WebHookValidation())->validateRefundResourceValues($dataToValidate)
        );
    }

    public function resourceDataProvider()
    {
        $allWrongDatas = new \stdClass();
        $allWrongDatas->value = null;
        $allWrongDatas->currency_code = null;

        $valueZeroError = new \stdClass();
        $valueZeroError->value = -1;
        $valueZeroError->currency_code = 'FR';

        $currencyError = new \stdClass();
        $currencyError->value = 10;
        $currencyError->currency_code = null;

        return array(
            array(
                array(
                    WebHookValidation::RESOURCE_DATA_ERROR,
                ),
                array(),
            ),
            array(
                array(
                    WebHookValidation::RESOURCE_VALUE_EMPTY_ERROR,
                    WebHookValidation::RESOURCE_VALUE_ZERO_ERROR,
                    WebHookValidation::RESOURCE_CURRENCY_ERROR,
                ),
                array(
                    'amount' => $allWrongDatas,
                ),
            ),
            array(
                array(
                    WebHookValidation::RESOURCE_VALUE_ZERO_ERROR,
                ),
                array(
                    'amount' => $valueZeroError,
                ),
            ),
            array(
                array(
                    WebHookValidation::RESOURCE_CURRENCY_ERROR,
                ),
                array(
                    'amount' => $currencyError,
                ),
            ),
        );
    }

    /**
     * @dataProvider orderDataProvider
     */
    public function testOrderDatas($resultExpect, $dataToValidate)
    {
        $this->assertSame(
            $resultExpect,
            (new WebHookValidation())->validateRefundOrderIdValue($dataToValidate)
        );
    }

    public function orderDataProvider()
    {
        return array(
            array(
                array(
                    WebHookValidation::ORDER_ERROR,
                ),
                '',
            ),
            array(
                array(),
                '68N82910RXXXXXXXX',
            ),
        );
    }
}
