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

namespace PsCheckout\Core\Tests\Unit\Hook\Handlers;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Hook\Handlers\CanceledPayPalOrderReconciliationHookHandler;
use PsCheckout\Core\Hook\Handlers\CanceledPayPalOrderReconciliationHookParams;
use PsCheckout\Core\PayPal\Order\Action\ReconcileCanceledPayPalOrdersActionInterface;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use Psr\Log\LoggerInterface;

class CanceledPayPalOrderReconciliationHookHandlerTest extends TestCase
{
    /** @var ConfigurationInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $configuration;

    /** @var ReconcileCanceledPayPalOrdersActionInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $action;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var CanceledPayPalOrderReconciliationHookHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->configuration = $this->createMock(ConfigurationInterface::class);
        $this->action = $this->createMock(ReconcileCanceledPayPalOrdersActionInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new CanceledPayPalOrderReconciliationHookHandler($this->configuration, $this->action, $this->logger);
    }

    public function testIsDueReturnsFalseWithinInterval(): void
    {
        $this->configuration->method('getInteger')
            ->with(CanceledPayPalOrderReconciliationHookHandler::LAST_RUN_CONFIGURATION_KEY)
            ->willReturn(time() - 60);

        $this->configuration->expects($this->never())->method('set');

        $this->assertFalse($this->handler->isDue());
    }

    public function testIsDueClaimsTheRunOnceIntervalElapsed(): void
    {
        $this->configuration->method('getInteger')
            ->willReturn(time() - CanceledPayPalOrderReconciliationHookHandler::INTERVAL_SECONDS - 1);

        $this->configuration->expects($this->once())
            ->method('set')
            ->with(
                CanceledPayPalOrderReconciliationHookHandler::LAST_RUN_CONFIGURATION_KEY,
                $this->greaterThanOrEqual(time())
            )
            ->willReturn(true);

        $this->assertTrue($this->handler->isDue());
    }

    public function testIsDueReturnsTrueWhenNeverRun(): void
    {
        $this->configuration->method('getInteger')->willReturn(0);
        $this->configuration->method('set')->willReturn(true);

        $this->assertTrue($this->handler->isDue());
    }

    public function testHandleRunsTheReconciliation(): void
    {
        $this->action->expects($this->once())->method('execute')->willReturn(2);
        $this->logger->expects($this->once())->method('info');

        $this->assertNull($this->handler->handle(new CanceledPayPalOrderReconciliationHookParams()));
    }

    public function testHandleSwallowsAndLogsFailures(): void
    {
        $this->action->method('execute')->willThrowException(new \RuntimeException('boom'));
        $this->logger->expects($this->once())->method('error');

        $this->assertNull($this->handler->handle(new CanceledPayPalOrderReconciliationHookParams()));
    }
}
