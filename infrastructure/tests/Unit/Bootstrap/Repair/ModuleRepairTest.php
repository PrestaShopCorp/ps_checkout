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

namespace Tests\Unit\PsCheckout\Infrastructure\Bootstrap\Repair;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Infrastructure\Adapter\ShopContextInterface;
use PsCheckout\Infrastructure\Bootstrap\Install\InstallerInterface;
use PsCheckout\Infrastructure\Bootstrap\Repair\ModuleRepair;
use Psr\Log\LoggerInterface;

class ModuleRepairTest extends TestCase
{
    private const HOOKS = ['paymentOptions', 'displayHeader'];

    /** @var \Module|MockObject */
    private $module;

    /** @var \Db|MockObject */
    private $db;

    /** @var InstallerInterface|MockObject */
    private $installer;

    /** @var ShopContextInterface|MockObject */
    private $shopContext;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var ModuleRepair */
    private $moduleRepair;

    protected function setUp(): void
    {
        $this->module = $this->createMock(\Module::class);
        $this->module->id = 12;
        $this->module->name = 'ps_checkout';
        $this->module->method('getTabs')->willReturn([
            ['class_name' => 'AdminAjaxPrestashopCheckout', 'visible' => false],
        ]);

        $this->db = $this->createMock(\Db::class);
        $this->db->method('escape')->willReturnArgument(0);
        $this->installer = $this->createMock(InstallerInterface::class);
        $this->shopContext = $this->createMock(ShopContextInterface::class);
        $this->shopContext->method('getCurrent')->willReturn(['context' => 1, 'shop_id' => 1, 'group_shop_id' => 1]);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->moduleRepair = new ModuleRepair(
            $this->module,
            $this->db,
            $this->installer,
            $this->shopContext,
            $this->logger
        );
    }

    public function testRepairIsNotNeededWhenTabAndHooksAreRegistered(): void
    {
        $this->mockDatabaseState(true, 25);

        $this->assertFalse($this->moduleRepair->isRepairNeeded());
    }

    public function testRepairIsNeededWhenTabIsMissing(): void
    {
        $this->mockDatabaseState(false, 25);

        $this->assertTrue($this->moduleRepair->isRepairNeeded());
    }

    public function testRepairIsNeededWhenNoHookIsRegistered(): void
    {
        $this->mockDatabaseState(true, 0);

        $this->assertTrue($this->moduleRepair->isRepairNeeded());
    }

    public function testRepairRunsInstallerAndRegistersHooksInAllShopContext(): void
    {
        $this->mockDatabaseState(true, 0);

        $this->shopContext->expects($this->once())->method('setAllShopContext');
        $this->shopContext->expects($this->once())->method('setContext')
            ->with(['context' => 1, 'shop_id' => 1, 'group_shop_id' => 1]);
        $this->installer->expects($this->once())->method('init')->willReturn(true);
        $this->module->expects($this->once())->method('registerHook')->with(self::HOOKS)->willReturn(true);

        $this->assertTrue($this->moduleRepair->repair(self::HOOKS));
    }

    public function testRepairFailsAndStillRegistersHooksWhenInstallerFails(): void
    {
        $this->mockDatabaseState(true, 0);

        $this->installer->method('init')->willReturn(false);
        $this->module->expects($this->once())->method('registerHook')->with(self::HOOKS)->willReturn(true);
        $this->logger->expects($this->atLeastOnce())->method('error');

        $this->assertFalse($this->moduleRepair->repair(self::HOOKS));
    }

    public function testRepairFailsAndRestoresShopContextWhenInstallerThrows(): void
    {
        $this->mockDatabaseState(true, 0);

        $this->installer->method('init')->willThrowException(new \RuntimeException('Table creation failed'));
        $this->module->method('registerHook')->willReturn(true);
        $this->shopContext->expects($this->once())->method('setContext');
        $this->logger->expects($this->atLeastOnce())->method('error');

        $this->assertFalse($this->moduleRepair->repair(self::HOOKS));
    }

    public function testRepairFailsWhenHookRegistrationFails(): void
    {
        $this->mockDatabaseState(true, 0);

        $this->installer->method('init')->willReturn(true);
        $this->module->method('registerHook')->willReturn(false);

        $this->assertFalse($this->moduleRepair->repair(self::HOOKS));
    }

    private function mockDatabaseState(bool $tabExists, int $registeredHooks): void
    {
        $this->db->method('getValue')->willReturnCallback(
            function (string $query) use ($tabExists, $registeredHooks) {
                if (strpos($query, 'hook_module') !== false) {
                    return (string) $registeredHooks;
                }

                return $tabExists ? '99' : false;
            }
        );
    }
}
