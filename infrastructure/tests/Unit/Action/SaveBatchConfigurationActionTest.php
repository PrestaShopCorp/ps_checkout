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

namespace Tests\Unit\PsCheckout\Infrastructure\Action;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Action\SaveBatchConfigurationAction;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use RuntimeException;

class SaveBatchConfigurationActionTest extends TestCase
{
    public function testExecuteSavesValidConfiguration()
    {
        $mockConfig = $this->createMock(ConfigurationInterface::class);
        $mockConfig->expects($this->once())
            ->method('set')
            ->with('PS_CHECKOUT_FOO', 'bar');

        $action = new SaveBatchConfigurationAction($mockConfig);
        $action->execute([['name' => 'PS_CHECKOUT_FOO', 'value' => 'bar']]);
    }

    public function testExecuteThrowsOnBlacklistedKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $mockConfig = $this->createMock(ConfigurationInterface::class);
        $action = new SaveBatchConfigurationAction($mockConfig);

        $action->execute([['name' => PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT, 'value' => 'attacker']]);
    }

    public function testExecuteThrowsOnInvalidFormat()
    {
        $this->expectException(InvalidArgumentException::class);

        $mockConfig = $this->createMock(ConfigurationInterface::class);
        $action = new SaveBatchConfigurationAction($mockConfig);

        $action->execute([['foo' => 'bar']]);
    }

    public function testExecuteThrowsOnPersistenceError()
    {
        $this->expectException(RuntimeException::class);

        $mockConfig = $this->createMock(ConfigurationInterface::class);
        $mockConfig->method('set')->will($this->throwException(new Exception('DB error')));

        $action = new SaveBatchConfigurationAction($mockConfig);
        $action->execute([['name' => 'PS_CHECKOUT_FOO', 'value' => 'bar']]);
    }
}
