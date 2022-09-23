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

namespace Tests\Unit\Webhook;

use Generator;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Webhook\WebhookEventConfigurationUpdatedHandler;

class WebhookEventConfigurationUpdatedHandlerTest extends TestCase
{
    /**
     * @dataProvider getSupportedWebhook
     *
     * @param array $data
     */
    public function testItIsSupportedWhenValidWebhookIsGiven(array $data)
    {
        $configurationMock = $this->createMock(PrestaShopConfiguration::class);
        $configurationMock->method('set')->willReturn('true');

        $webhookHandler = new WebhookEventConfigurationUpdatedHandler($configurationMock);
        $this->assertTrue($webhookHandler->supports($data['payload']));
    }

    /**
     * @dataProvider getUnsupportedWebhook
     *
     * @param array $data
     */
    public function testItIsUnsupportedWhenInvalidWebhookIsGiven(array $data)
    {
        $configurationMock = $this->createMock(PrestaShopConfiguration::class);
        $configurationMock->method('set')->willReturn('true');

        $webhookHandler = new WebhookEventConfigurationUpdatedHandler($configurationMock);
        $this->assertFalse($webhookHandler->supports($data['payload']));
    }

    /**
     * @dataProvider getValidWebhook
     *
     * @param array $data
     */
    public function testItIsHandledSuccessfullyWhenValidWebhookIsGiven(array $data)
    {
        $configurationMock = $this->createMock(PrestaShopConfiguration::class);
        $configurationMock->method('set')->willReturn('true');

        $webhookHandler = new WebhookEventConfigurationUpdatedHandler($configurationMock);
        $webhookHandler->handle($data['payload']);
    }

    /**
     * @dataProvider getInvalidWebhook
     *
     * @param array $data
     */
    public function testItIsFailWhenInvalidWebhookIsGiven(array $data)
    {
        $this->expectException(\Exception::class);

        $configurationMock = $this->createMock(PrestaShopConfiguration::class);
        $configurationMock->method('set')->willReturn('true');

        $webhookHandler = new WebhookEventConfigurationUpdatedHandler($configurationMock);
        $webhookHandler->handle($data['payload']);
    }

    /**
     * @return Generator
     */
    public function getSupportedWebhook()
    {
        yield [[
            'payload' => [
                'id' => '246154d6-2fc3-400d-b5e2-3e3918338092',
                'createTime' => '2022-09-20T06:56:46.730Z',
                'resourceType' => 'configuration',
                'eventType' => 'PRESTASHOP.CONFIGURATION.UPDATED',
                'summary' => 'PrestaShop configuration should be updated',
                'resource' => [
                    'configuration' => [
                        [
                            'name' => 'PS_CHECKOUT_PAYPAL_ID_MERCHANT',
                            'value' => 'J8SD6GXHCALCA',
                        ],
                    ],
                ],
                'links' => [],
                'eventVersion' => '1.0.0',
            ],
        ]];
    }

    /**
     * @return Generator
     */
    public function getUnsupportedWebhook()
    {
        yield [[
            'payload' => [
                'id' => '246154d6-2fc3-400d-b5e2-3e3918338092',
                'createTime' => '2022-09-20T06:56:46.730Z',
                'resourceType' => 'payment',
                'eventType' => 'CHECKOUT.PAYMENT-APPROVAL.REVERSED',
                'summary' => 'A payment has been reversed after approval.',
                'resource' => [
                    'order_id' => '5O190127TN364715T',
                ],
                'links' => [],
                'eventVersion' => '1.0.0',
            ],
        ]];
    }

    /**
     * @return Generator
     */
    public function getValidWebhook()
    {
        yield [[
            'payload' => [
                'id' => '246154d6-2fc3-400d-b5e2-3e3918338092',
                'createTime' => '2022-09-20T06:56:46.730Z',
                'resourceType' => 'configuration',
                'eventType' => 'PRESTASHOP.CONFIGURATION.UPDATED',
                'summary' => 'PrestaShop configuration should be updated',
                'resource' => [
                    'configuration' => [
                        [
                            'name' => 'PS_CHECKOUT_PAYPAL_ID_MERCHANT',
                            'value' => 'J8SD6GXHCALCA',
                        ],
                    ],
                ],
                'links' => [],
                'eventVersion' => '1.0.0',
            ],
        ]];
    }

    /**
     * @return Generator
     */
    public function getInvalidWebhook()
    {
        yield [
            [
                'payload' => [
                    'id' => '246154d6-2fc3-400d-b5e2-3e3918338092',
                    'createTime' => '2022-09-20T06:56:46.730Z',
                    'resourceType' => 'configuration',
                    'summary' => 'PrestaShop configuration should be updated',
                    'resource' => [
                    ],
                    'links' => [],
                    'eventVersion' => '1.0.0',
                ],
            ],
            [
                'payload' => [
                    'id' => '246154d6-2fc3-400d-b5e2-3e3918338092',
                    'createTime' => '2022-09-20T06:56:46.730Z',
                    'resourceType' => 'configuration',
                    'summary' => 'PrestaShop configuration should be updated',
                    'resource' => [
                        'configuration' => [
                            [
                                'name' => 'PS_OS_PAYMENT',
                                'value' => '2',
                            ],
                        ],
                    ],
                    'links' => [],
                    'eventVersion' => '1.0.0',
                ],
            ],
        ];
    }
}
