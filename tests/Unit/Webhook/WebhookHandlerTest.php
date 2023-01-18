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
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Webhook\WebhookEventHandlerInterface;
use PrestaShop\Module\PrestashopCheckout\Webhook\WebhookHandler;
use PrestaShop\Module\PrestashopCheckout\Webhook\WebhookSecretTokenService;

class WebhookHandlerTest extends TestCase
{
    /**
     * @dataProvider getValidWebhook
     *
     * @param array $data
     *
     * @throws PsCheckoutException
     */
    public function testItWorksWithValidSecretIsGiven(array $data)
    {
        $webhookEventHandlerMock = $this->createMock(WebhookEventHandlerInterface::class);
        $webhookEventHandlerMock->method('supports')->willReturn(true);
        $webhookEventHandlerMock->method('handle')->willReturn(true);

        $webhookSecretTokenServiceMock = $this->createMock(WebhookSecretTokenService::class);
        $webhookSecretTokenServiceMock->method('validateSecretToken')->willReturn(true);

        $webhookHandler = new WebhookHandler($webhookSecretTokenServiceMock, [$webhookEventHandlerMock]);
        $this->assertTrue($webhookHandler->authenticate($data['secret']));
    }

    /**
     * @dataProvider getInvalidWebhook
     *
     * @param array $data
     *
     * @throws PsCheckoutException
     */
    public function testItFailWithInvalidSecretIsGiven(array $data)
    {
        $webhookEventHandlerMock = $this->createMock(WebhookEventHandlerInterface::class);
        $webhookEventHandlerMock->method('supports')->willReturn(true);
        $webhookEventHandlerMock->method('handle')->willReturn(true);

        $webhookSecretTokenServiceMock = $this->createMock(WebhookSecretTokenService::class);
        $webhookSecretTokenServiceMock->method('validateSecretToken')->willReturn(false);

        $webhookHandler = new WebhookHandler($webhookSecretTokenServiceMock, [$webhookEventHandlerMock]);
        $this->assertFalse($webhookHandler->authenticate($data['secret']));
    }

    /**
     * @dataProvider getValidWebhook
     *
     * @param array $data
     *
     * @throws PsCheckoutException
     */
    public function testItIsHandledSuccessfullyWhenValidWebhookIsGiven(array $data)
    {
        $webhookEventHandlerMock = $this->createMock(WebhookEventHandlerInterface::class);
        $webhookEventHandlerMock->method('supports')->willReturn(true);
        $webhookEventHandlerMock->method('handle')->willReturn(true);

        $webhookSecretTokenServiceMock = $this->createMock(WebhookSecretTokenService::class);
        $webhookSecretTokenServiceMock->method('validateSecretToken')->willReturn(true);

        $webhookHandler = new WebhookHandler($webhookSecretTokenServiceMock, [$webhookEventHandlerMock]);
        $webhookHandler->handle($data['payload']);
    }

    /**
     * @dataProvider getInvalidWebhook
     *
     * @param array $data
     *
     * @throws PsCheckoutException
     */
    public function testItIsFailWhenInvalidWebhookIsGiven(array $data)
    {
        $this->expectException(\Exception::class);

        $webhookEventHandlerMock = $this->createMock(WebhookEventHandlerInterface::class);
        $webhookEventHandlerMock->method('supports')->willReturn(false);
        $webhookEventHandlerMock->method('handle')->willReturn(false);

        $webhookSecretTokenServiceMock = $this->createMock(WebhookSecretTokenService::class);
        $webhookSecretTokenServiceMock->method('validateSecretToken')->willReturn(true);

        $webhookHandler = new WebhookHandler($webhookSecretTokenServiceMock, [$webhookEventHandlerMock]);
        $webhookHandler->handle($data['payload']);
    }

    /**
     * @return Generator
     */
    public function getValidWebhook()
    {
        yield [[
            'secret' => 'secret',
            'payload' => [
                'eventType' => 'PRESTASHOP.CONFIGURATION.UPDATED',
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
                'secret' => 'bad-secret',
                'payload' => [],
            ],
        ];
    }
}
