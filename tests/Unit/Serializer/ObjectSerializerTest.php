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

namespace Serializer;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceEntity;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\CardResponse;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\CreatePayPalOrderResponse;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\LinkDescription;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\PaymentSourceResponse;
use PrestaShop\Module\PrestashopCheckout\Serializer\ObjectSerializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class ObjectSerializerTest extends TestCase
{
    /**
     * @dataProvider objectProvider
     */
    public function testSerializeWithoutEmptyValues($object, $expectedJson)
    {
        echo 'Test testSerializeWithoutEmptyValues';
        $serializer = new ObjectSerializer();
        $json = $serializer->serialize($object, JsonEncoder::FORMAT, true);
        $this->assertEquals($expectedJson, $json);
    }

    /**
     * @dataProvider objectWithEmptyValuesProvider
     */
    public function testSerializeWithEmptyValues($object, $expectedJson)
    {
        $serializer = new ObjectSerializer();
        $json = $serializer->serialize($object, JsonEncoder::FORMAT);
        $this->assertEquals($expectedJson, $json);
    }

    /**
     * @dataProvider objectProvider
     */
    public function testDeserialize($expectedObject, $json)
    {
        $serializer = new ObjectSerializer();
        $newObject = $serializer->deserialize($json, FundingSourceEntity::class, JsonEncoder::FORMAT);
        $this->assertEquals($expectedObject, $newObject);
    }

    /**
     * @dataProvider createPayPalOrderResponseSerializeObjectProvider
     */
    public function testSerializePayPalOrderResponse($object, $expectedJson)
    {
        $serializer = new ObjectSerializer();
        $json = $serializer->serialize($object, JsonEncoder::FORMAT, true, true);
        $this->assertEquals($expectedJson, $json);
    }

    /**
     * @dataProvider createPayPalOrderResponseDeserializeObjectProvider
     */
    public function testDeserializePayPalOrderResponse($expectedObject, $json)
    {
        $serializer = new ObjectSerializer();
        $newObject = $serializer->deserialize($json, CreatePayPalOrderResponse::class, JsonEncoder::FORMAT);
        $this->assertEquals($expectedObject, $newObject);
    }

    /**
     * @dataProvider arrayProvider
     */
    public function testToArray($object, $expectedArray, $skipNullValues, $convertToSnakeCase)
    {
        $serializer = new ObjectSerializer();
        $newArray = $serializer->toArray($object, $skipNullValues, $convertToSnakeCase);
        $this->assertEquals($expectedArray, $newArray);
    }


    public function objectProvider()
    {
        $fundingSourceEntity = new FundingSourceEntity('paypal');
        $fundingSourceEntity->setCountries(['US', 'FR']);
        $fundingSourceEntity->setPosition(0);

        return [
            [
                new FundingSourceEntity('paypal'),
                '{"name":"paypal","countries":[],"isEnabled":true,"isToggleable":true}',
            ],
            [
                $fundingSourceEntity,
                '{"name":"paypal","position":0,"countries":["US","FR"],"isEnabled":true,"isToggleable":true}',
            ],
        ];
    }

    public function objectWithEmptyValuesProvider()
    {
        $fundingSourceEntity = new FundingSourceEntity('paypal');
        $fundingSourceEntity->setCountries(['US', 'FR']);
        $fundingSourceEntity->setPosition(0);

        return [
            [
                new FundingSourceEntity('paypal'),
                '{"name":"paypal","position":null,"countries":[],"isEnabled":true,"isToggleable":true}',
            ],
            [
                $fundingSourceEntity,
                '{"name":"paypal","position":0,"countries":["US","FR"],"isEnabled":true,"isToggleable":true}',
            ],
        ];
    }

    public function createPayPalOrderResponseSerializeObjectProvider()
    {
        return [
            [
                (new CreatePayPalOrderResponse())
                    ->setId('SOME_ID')
                    ->setLinks([new LinkDescription(['href' => 'HREF', 'rel' => 'REL', 'method' => 'METHOD'])]),
                '{"id":"SOME_ID","links":[{"href":"HREF","rel":"REL","method":"METHOD"}]}',
            ],
            [
                (new CreatePayPalOrderResponse())
                    ->setId('SOME_ID')
                    ->setLinks([new LinkDescription(['href' => 'HREF', 'rel' => 'REL', 'method' => 'METHOD'])])
                    ->setPaymentSource(new PaymentSourceResponse(['card' => new CardResponse(['name' => 'AMEX'])])),
                '{"id":"SOME_ID","payment_source":{"card":{"name":"AMEX"}},"links":[{"href":"HREF","rel":"REL","method":"METHOD"}]}',
            ],
        ];
    }

    public function createPayPalOrderResponseDeserializeObjectProvider()
    {
        return [
            [
                (new CreatePayPalOrderResponse())
                    ->setId('SOME_ID')
                    ->setLinks([new LinkDescription(['href' => 'HREF', 'rel' => 'REL', 'method' => 'METHOD'])]),
                '{"id":"SOME_ID","links":[{"href":"HREF","rel":"REL","method":"METHOD"}]}',
            ],
            [
                (new CreatePayPalOrderResponse())
                    ->setId('SOME_ID')
                    ->setLinks([new LinkDescription(['href' => 'HREF', 'rel' => 'REL', 'method' => 'METHOD'])])
                    ->setPaymentSource(new PaymentSourceResponse(['card' => new CardResponse(['name' => 'AMEX'])])),
                '{"id":"SOME_ID","payment_source":{"card":{"name":"AMEX"}},"links":[{"href":"HREF","rel":"REL","method":"METHOD"}]}',
            ],
            [
                (new CreatePayPalOrderResponse())
                    ->setId('SOME_ID')
                    ->setLinks([new LinkDescription(['href' => 'HREF', 'rel' => 'REL', 'method' => 'METHOD'])])
                    ->setPaymentSource(new PaymentSourceResponse(['card' => new CardResponse(['name' => 'AMEX'])])),
                '{"id": "SOME_ID","cart_id":"RANDOM_CART_ID","payment_source":{"card":{"name":"AMEX"}},"links":[{"href":"HREF","rel":"REL","method":"METHOD"}]}',
            ],
        ];
    }

    public function arrayProvider()
    {
        return [
            [
                (new CreatePayPalOrderResponse())
                    ->setId('SOME_ID')
                    ->setLinks([new LinkDescription(['href' => 'HREF', 'rel' => 'REL', 'method' => 'METHOD'])])
                    ->setPaymentSource(new PaymentSourceResponse(['card' => new CardResponse(['name' => 'AMEX'])])),
                [
                    'id' => 'SOME_ID',
                    'create_time' => null,
                    'update_time' => null,
                    'payment_source' => [
                        'card' => [
                            'name' => 'AMEX',
                            'last_digits' => null,
                            'brand' => null,
                            'available_networks' => null,
                            'type' => null,
                            'authentication_result' => null,
                            'attributes' => null,
                            'from_request' => null,
                            'expiry' => null,
                            'bin_details' => null
                        ],
                        'paypal' => null,
                        'bancontact' => null,
                        'blik' => null,
                        'eps' => null,
                        'giropay' => null,
                        'ideal' => null,
                        'mybank' => null,
                        'p24' => null,
                        'sofort' => null,
                        'trustly' => null,
                        'venmo' => null
                    ],
                    'intent' => null,
                    'processing_instruction' => null,
                    'payer' => null,
                    'purchase_units' => null,
                    'status' => null,
                    'links' => [
                        ['href' => 'HREF', 'rel' => 'REL', 'method' => 'METHOD']
                    ],
                ],
                false,
                true
            ],
            [
                (new CreatePayPalOrderResponse())
                    ->setId('SOME_ID')
                    ->setLinks([new LinkDescription(['href' => 'HREF', 'rel' => 'REL', 'method' => 'METHOD'])])
                    ->setPaymentSource(new PaymentSourceResponse(['card' => new CardResponse(['name' => 'AMEX'])])),
                [
                    'id' => 'SOME_ID',
                    'payment_source' => [
                        'card' => [
                            'name' => 'AMEX'
                        ]
                    ],
                    'links' => [
                        ['href' => 'HREF', 'rel' => 'REL', 'method' => 'METHOD']
                    ],
                ],
                true,
                true
            ],
        ];
    }
}
