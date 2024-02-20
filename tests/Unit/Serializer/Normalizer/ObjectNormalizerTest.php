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

namespace Tests\Unit\Serializer\Normalizer;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceEntity;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\CardResponse;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\CreatePayPalOrderResponse;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\LinkDescription;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\PaymentSourceResponse;
use PrestaShop\Module\PrestashopCheckout\Serializer\Normalizer\ObjectNormalizer;
use PrestaShop\Module\PrestashopCheckout\Serializer\ObjectSerializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class ObjectNormalizerTest extends TestCase
{
    /**
     * @dataProvider objectProvider
     */
    public function testSerialize($object, $expectedJson)
    {
        $serializer = new ObjectSerializer();
        $json = $serializer->serialize($object, JsonEncoder::FORMAT, [ObjectNormalizer::PS_SKIP_NULL_VALUES => true]);
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
     * @dataProvider createPayPalOrderResponseObjectProvider
     */
    public function testDeserializePayPalOrderResponse($expectedObject, $json)
    {
        $serializer = new ObjectSerializer();
        $newObject = $serializer->deserialize($json, CreatePayPalOrderResponse::class, JsonEncoder::FORMAT);
        $this->assertEquals($expectedObject, $newObject);
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

    public function createPayPalOrderResponseObjectProvider()
    {
        return [
            [
                (new CreatePayPalOrderResponse())
                    ->setId('SOME_ID')
                    ->setLinks([new LinkDescription(['href' => 'HREF', 'rel' => 'REL', 'method' => 'METHOD'])]),
                '{"id": "SOME_ID", "links": [{"href":"HREF","rel":"REL","method":"METHOD"}]}',
            ],
            [
                (new CreatePayPalOrderResponse())
                    ->setId('SOME_ID')
                    ->setLinks([new LinkDescription(['href' => 'HREF', 'rel' => 'REL', 'method' => 'METHOD'])])
                    ->setPaymentSource(new PaymentSourceResponse(['card' => new CardResponse(['name' => 'AMEX'])])),
                '{"id": "SOME_ID","payment_source": {"card": {"name":"AMEX"}},"links": [{"href":"HREF","rel":"REL","method":"METHOD"}]}',
            ],
            [
                (new CreatePayPalOrderResponse())
                    ->setId('SOME_ID')
                    ->setLinks([new LinkDescription(['href' => 'HREF', 'rel' => 'REL', 'method' => 'METHOD'])])
                    ->setPaymentSource(new PaymentSourceResponse(['card' => new CardResponse(['name' => 'AMEX'])])),
                '{"id": "SOME_ID","cart_id": "RANDOM_CART_ID", "payment_source": {"card": {"name":"AMEX"}},"links": [{"href":"HREF","rel":"REL","method":"METHOD"}]}',
            ],
        ];
    }
}
