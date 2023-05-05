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

namespace Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfigurationOptionsResolver;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Temp\Factory\OrderDataFactory;
use PrestaShop\Module\PrestashopCheckout\Temp\Adapter\OrderDataAdapter;

class OrderPayloadBuilderTest extends TestCase
{
    public function testPayloadCreation()
    {
        $orderDataAdapterMock = $this->createMock(OrderDataAdapter::class);
        $orderDataAdapterMock->method('getGenderName')->willReturn('M');
        $orderDataAdapterMock->method('getStateName')->willReturn('Ile de France');
        $orderDataAdapterMock->method('getIsoCountry')->willReturn('FR');

        $accountRepository = new PaypalAccountRepository(new PrestaShopConfiguration(new PrestaShopConfigurationOptionsResolver(1)));
        $orderFactory = new OrderDataFactory($accountRepository, $orderDataAdapterMock);
        $payload = $orderFactory->createFromArray($this->getDataPayload());
        $this->checkPayloadPayer($payload);
        $this->checkPayloadApplicationContext($payload);
        $this->checkPayloadPurchaseUnits($payload);
    }

    public function testPayloadChecksum()
    {
        $orderDataAdapterMock = $this->createMock(OrderDataAdapter::class);
        $orderDataAdapterMock->method('getGenderName')->willReturn('M');
        $orderDataAdapterMock->method('getStateName')->willReturn('Ile de France');
        $orderDataAdapterMock->method('getIsoCountry')->willReturn('FR');

        $accountRepository = new PaypalAccountRepository(new PrestaShopConfiguration(new PrestaShopConfigurationOptionsResolver(1)));
        $orderFactory = new OrderDataFactory($accountRepository, $orderDataAdapterMock);
        $dataPayload = $this->getDataPayload();
        $payload = $orderFactory->createFromArray($dataPayload, false);
        $dataPayload['payer']['address_line_1'] = '79 avenue des Champs';
        $payload2 = $orderFactory->createFromArray($dataPayload, false);
        $this->checkPayloadChecksumPayer($payload, $payload2);
    }

    /**
     * @return array
     */
    public function getDataPayload()
    {
        return [
            'cart' => [
                'id' => 3,
                'id_lang' => 1,
                'items' => $this->getCartItems(),
                'shipping_cost' => 5.70,
                'subtotals' => [
                    'gift_wrapping' => [
                        'amount' => 0
                    ]
                ],
                'total_with_taxes' => 83
            ],
            'currency' => [
                'iso_code' => 1
            ],
            'customer' => [
                'birthday' => '1990-01-01',
                'email_address' => 'john.doe@mail.fr',
                'id_gender' => 1
            ],
            'payee' => [
                'email_address' => 'nhoj.eod@prestatest.fr',
                'merchant_id' => '716537O08'
            ],
            'payer' => [
                'address_line_1' => '16 rue des champs',
                'address_line_2' => 'Appartement 23',
                'admin_area_2' => 'Paris',
                'given_name' => 'John',
                'id_country' => 1,
                'id_state' => 1,
                'surname' => 'Doe',
                'payer_id' => '',
                'phone' => '',
                'phone_mobile' => '0612345678',
                'postcode' => '75000'
            ],
            'psCheckout' => [
                'isExpressCheckout' => false
            ],
            'shipping' => [
                'address_line_1' => '5 rue du port',
                'address_line_2' => 'Appartement 4',
                'admin_area_2' => 'Le Mans',
                'given_name' => 'Johnny',
                'id_country' => 1,
                'id_state' => 1,
                'surname' => 'Doe',
                'postcode' => '72000'
            ],
            'shop' => [
                'name' => 'PrestaTest'
            ]
        ];
    }

    /**
     * @return array
     */
    public function getCartItems()
    {
        return [
            [
                'attributes' => 'Coupe classique, col rond, manches courtes',
                'is_virtual' => '0',
                'name' => 'Pull imprime',
                'quantity' => 2,
                'total' => 37.80,
                'total_wt' => 40
            ],
            [
                'attributes' => 'Mug rouge en ceramique, avec une anse',
                'is_virtual' => '0',
                'name' => 'Mug',
                'quantity' => 1,
                'total' => 2.60,
                'total_wt' => 3
            ]
        ];
    }

    /**
     * @param array $payload
     */
    private function checkPayloadPayer($payload)
    {
        print 'Payer : ' . json_encode($payload['payer']) . PHP_EOL;
    }

    /**
     * @param array $payload
     */
    private function checkPayloadApplicationContext($payload)
    {
        print 'ApplicationContext : ' . json_encode($payload['application_context']) . PHP_EOL;
    }

    /**
     * @param array $payload
     */
    private function checkPayloadPurchaseUnits($payload)
    {
        print 'PurchaseUnits : ' . json_encode($payload['purchase_units']) . PHP_EOL;
    }

    /**
     * @param array $payload
     */
    private function checkPayloadChecksumPayer($payload, $payload2)
    {
        $checksum = $payload['payer']->generateChecksum();
        $checksum2 = $payload2['payer']->generateChecksum();
        $this->assertTrue($checksum !== $checksum2,'Two different checksum for the Payer object');
    }
}
