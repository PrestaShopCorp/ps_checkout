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
        return [
            [
                [
                    WebHookValidation::HEADER_DATA_ERROR,
                ],
                [],
            ],
            [
                [
                    WebHookValidation::HEADER_SHOPID_ERROR,
                    WebHookValidation::HEADER_MERCHANTID_ERROR,
                    WebHookValidation::HEADER_PSXID_ERROR,
                ],
                [
                    'Shop-Id' => '',
                    'Merchant-Id' => '',
                    'Psx-Id' => '',
                ],
            ],
            [
                [
                    WebHookValidation::HEADER_SHOPID_ERROR,
                ],
                [
                    'Shop-Id' => '',
                    'Merchant-Id' => 'SZKJZHXXXXXXX',
                    'Psx-Id' => 'wVH5CmKq4XeJkXXXXXXXXXXXXXXX',
                ],
            ],
            [
                [
                    WebHookValidation::HEADER_MERCHANTID_ERROR,
                ],
                [
                    'Shop-Id' => '9a053ac6-9e7c-4c75-b57d-XXXXXXXX',
                    'Merchant-Id' => '',
                    'Psx-Id' => 'wVH5CmKq4XeJkXXXXXXXXXXXXXXX',
                ],
            ],
            [
                [
                    WebHookValidation::HEADER_PSXID_ERROR,
                ],
                [
                    'Shop-Id' => '9a053ac6-9e7c-4c75-b57d-XXXXXXXX',
                    'Merchant-Id' => 'SZKJZHXXXXXXX',
                    'Psx-Id' => '',
                ],
            ],
            [
                [
                    WebHookValidation::HEADER_PSXID_ERROR,
                ],
                [
                    'Shop-Id' => '9a053ac6-9e7c-4c75-b57d-XXXXXXXX',
                    'Merchant-Id' => 'SZKJZHXXXXXXX',
                ],
            ],
            [
                [],
                [
                    'Shop-Id' => '9a053ac6-9e7c-4c75-b57d-XXXXXXXX',
                    'Merchant-Id' => 'SZKJZHXXXXXXX',
                    'Psx-Id' => 'wVH5CmKq4XeJkXXXXXXXXXXXXXXX',
                ],
            ],
        ];
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
        return [
            [
                [
                    WebHookValidation::BODY_DATA_ERROR,
                ],
                [],
            ],
            [
                [
                    WebHookValidation::BODY_EVENTTYPE_ERROR,
                    WebHookValidation::BODY_CATEGORY_ERROR,
                    WebHookValidation::BODY_RESOURCE_ERROR,
                ],
                [
                    'eventType' => '',
                    'category' => '',
                    'resource' => '',
                ],
            ],
            [
                [
                    WebHookValidation::BODY_EVENTTYPE_ERROR,
                ],
                [
                    'eventType' => '',
                    'category' => 'ShopNotificationMerchantAccount',
                    'resource' => [
                        'amount',
                    ],
                ],
            ],
            [
                [
                    WebHookValidation::BODY_CATEGORY_ERROR,
                ],
                [
                    'eventType' => 'PaymentCaptureRefunded',
                    'category' => '',
                    'resource' => [
                        'amount',
                    ],
                ],
            ],
            [
                [
                    WebHookValidation::BODY_RESOURCE_ERROR,
                ],
                [
                    'eventType' => 'PaymentCaptureRefunded',
                    'category' => 'ShopNotificationMerchantAccount',
                    'resource' => [],
                ],
            ],
            [
                [
                    WebHookValidation::BODY_RESOURCE_ERROR,
                ],
                [
                    'eventType' => 'PaymentCaptureRefunded',
                    'category' => 'ShopNotificationMerchantAccount',
                ],
            ],
            [
                [],
                [
                    'eventType' => 'PaymentCaptureRefunded',
                    'category' => 'ShopNotificationMerchantAccount',
                    'resource' => [
                        'amount',
                    ],
                ],
            ],
        ];
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

        return [
            [
                [
                    WebHookValidation::RESOURCE_DATA_ERROR,
                ],
                [],
            ],
            [
                [
                    WebHookValidation::RESOURCE_VALUE_EMPTY_ERROR,
                    WebHookValidation::RESOURCE_VALUE_ZERO_ERROR,
                    WebHookValidation::RESOURCE_CURRENCY_ERROR,
                ],
                [
                    'amount' => $allWrongDatas,
                ],
            ],
            [
                [
                    WebHookValidation::RESOURCE_VALUE_ZERO_ERROR,
                ],
                [
                    'amount' => $valueZeroError,
                ],
            ],
            [
                [
                    WebHookValidation::RESOURCE_CURRENCY_ERROR,
                ],
                [
                    'amount' => $currencyError,
                ],
            ],
        ];
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
        return [
            [
                [
                    WebHookValidation::ORDER_ERROR,
                ],
                '',
            ],
            [
                [],
                '68N82910RXXXXXXXX',
            ],
        ];
    }
}
