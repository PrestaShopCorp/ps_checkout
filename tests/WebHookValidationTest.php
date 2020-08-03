<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\WebHookValidation;

class WebHookValidationTest extends TestCase
{
    /**
     * @throws PsCheckoutException
     */
    public function testHeaderDataWithValidParams()
    {
        $webHookValidation = new WebHookValidation();
        $this->assertTrue($webHookValidation->validateHeaderDatas([
            'Shop-Id' => '9a053ac6-9e7c-4c75-b57d-XXXXXXXX',
            'Merchant-Id' => 'SZKJZHXXXXXXX',
            'Psx-Id' => 'wVH5CmKq4XeJkXXXXXXXXXXXXXXX',
        ]));
    }

    /**
     * @dataProvider headerDataProvider
     *
     * @param int $exceptionCodeExpected
     * @param array $dataToValidate
     *
     * @throws PsCheckoutException
     */
    public function testHeaderDataWithInvalidParams($exceptionCodeExpected, array $dataToValidate)
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode($exceptionCodeExpected);

        $webHookValidation = new WebHookValidation();
        $webHookValidation->validateHeaderDatas($dataToValidate);
    }

    /**
     * @return array[]
     */
    public function headerDataProvider()
    {
        return [
            [
                PsCheckoutException::PSCHECKOUT_WEBHOOK_HEADER_EMPTY,
                [],
            ],
            [
                PsCheckoutException::PSCHECKOUT_WEBHOOK_SHOP_ID_EMPTY,
                [
                    'Shop-Id' => '',
                    'Merchant-Id' => 'SZKJZHXXXXXXX',
                    'Psx-Id' => 'wVH5CmKq4XeJkXXXXXXXXXXXXXXX',
                ],
            ],
            [
                PsCheckoutException::PSCHECKOUT_WEBHOOK_MERCHANT_ID_EMPTY,
                [
                    'Shop-Id' => '9a053ac6-9e7c-4c75-b57d-XXXXXXXX',
                    'Merchant-Id' => '',
                    'Psx-Id' => 'wVH5CmKq4XeJkXXXXXXXXXXXXXXX',
                ],
            ],
            [
                PsCheckoutException::PSCHECKOUT_WEBHOOK_PSX_ID_EMPTY,
                [
                    'Shop-Id' => '9a053ac6-9e7c-4c75-b57d-XXXXXXXX',
                    'Merchant-Id' => 'SZKJZHXXXXXXX',
                    'Psx-Id' => '',
                ],
            ],
        ];
    }

    /**
     * @throws PsCheckoutException
     */
    public function testBodyDataWithValidParams()
    {
        $webHookValidation = new WebHookValidation();
        $this->assertTrue($webHookValidation->validateBodyDatas([
            'eventType' => 'PaymentCaptureRefunded',
            'category' => 'ShopNotificationMerchantAccount',
            'resource' => [
                'amount',
            ],
        ]));
    }

    /**
     * @dataProvider bodyDataProvider
     *
     * @param int $exceptionCodeExpected
     * @param array $dataToValidate
     *
     * @throws PsCheckoutException
     */
    public function testBodyDataWithInvalidParams($exceptionCodeExpected, $dataToValidate)
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode($exceptionCodeExpected);

        $webHookValidation = new WebHookValidation();
        $webHookValidation->validateBodyDatas($dataToValidate);
    }

    /**
     * @return array[]
     */
    public function bodyDataProvider()
    {
        return [
            [
                PsCheckoutException::PSCHECKOUT_WEBHOOK_BODY_EMPTY,
                [],
            ],
            [
                PsCheckoutException::PSCHECKOUT_WEBHOOK_EVENT_TYPE_EMPTY,
                [
                    'eventType' => '',
                    'category' => 'ShopNotificationMerchantAccount',
                    'resource' => [
                        'amount',
                    ],
                ],
            ],
            [
                PsCheckoutException::PSCHECKOUT_WEBHOOK_CATEGORY_EMPTY,
                [
                    'eventType' => 'PaymentCaptureRefunded',
                    'category' => '',
                    'resource' => [
                        'amount',
                    ],
                ],
            ],
            [
                PsCheckoutException::PSCHECKOUT_WEBHOOK_RESOURCE_EMPTY,
                [
                    'eventType' => 'PaymentCaptureRefunded',
                    'category' => 'ShopNotificationMerchantAccount',
                ],
            ],
        ];
    }

    /**
     * @throws PsCheckoutException
     */
    public function testResourceDataWithValidParams()
    {
        $webHookValidation = new WebHookValidation();
        $this->assertTrue($webHookValidation->validateRefundResourceValues([
            'amount' => [
                'value' => '12.00',
                'currency_code' => 'EUR',
            ],
        ]));
    }

    /**
     * @dataProvider resourceDataProvider
     *
     * @param int $exceptionCodeExpected
     * @param array $dataToValidate
     *
     * @throws PsCheckoutException
     */
    public function testResourceDataWithInvalidParams($exceptionCodeExpected, $dataToValidate)
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode($exceptionCodeExpected);

        $webHookValidation = new WebHookValidation();
        $webHookValidation->validateRefundResourceValues($dataToValidate);
    }

    /**
     * @return array[]
     */
    public function resourceDataProvider()
    {
        return [
            [
                PsCheckoutException::PSCHECKOUT_WEBHOOK_RESOURCE_EMPTY,
                [],
            ],
            [
                PsCheckoutException::PSCHECKOUT_WEBHOOK_AMOUNT_EMPTY,
                [
                    'amount' => [
                        'value' => '',
                        'currency_code' => 'EUR',
                    ],
                ],
            ],
            [
                PsCheckoutException::PSCHECKOUT_WEBHOOK_AMOUNT_INVALID,
                [
                    'amount' => [
                        'value' => '0',
                        'currency_code' => 'EUR',
                    ],
                ],
            ],
            [
                PsCheckoutException::PSCHECKOUT_WEBHOOK_CURRENCY_EMPTY,
                [
                    'amount' => [
                        'value' => '12.00',
                        'currency_code' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @throws PsCheckoutException
     */
    public function testOrderDataWithValidParams()
    {
        $webHookValidation = new WebHookValidation();
        $this->assertTrue($webHookValidation->validateRefundOrderIdValue('68N82910RXXXXXXXX'));
    }

    /**
     * @dataProvider orderDataProvider
     *
     * @param int $exceptionCodeExpected
     * @param string $dataToValidate
     *
     * @throws PsCheckoutException
     */
    public function testOrderDataWithInvalidParams($exceptionCodeExpected, $dataToValidate)
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode($exceptionCodeExpected);

        $webHookValidation = new WebHookValidation();
        $webHookValidation->validateRefundOrderIdValue($dataToValidate);
    }

    /**
     * @return array[]
     */
    public function orderDataProvider()
    {
        return [
            [
                PsCheckoutException::PSCHECKOUT_WEBHOOK_ORDER_ID_EMPTY,
                '',
            ],
        ];
    }
}
