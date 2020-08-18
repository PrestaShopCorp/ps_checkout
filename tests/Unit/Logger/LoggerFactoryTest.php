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

namespace Tests\Unit\Logger;

use Generator;
use Monolog\Handler\HandlerInterface;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFactory;
use TypeError;

class LoggerFactoryTest extends TestCase
{
    /**
     * @dataProvider getValidValue
     *
     * @param array $data
     *
     * @throws PsCheckoutException
     */
    public function testItIsCreatedSuccessfullyWhenValidValueIsGiven($data)
    {
        new LoggerFactory($data['name'], $data['loggerHandler']);
    }

    /**
     * @dataProvider getInvalidValue
     *
     * @param array $data
     *
     * @throws PsCheckoutException
     */
    public function testItThrowsExceptionWhenInvalidValueIsGiven($data)
    {
        if ($data['loggerHandler'] instanceof HandlerInterface) {
            $this->expectException(PsCheckoutException::class);
            $this->expectExceptionCode(PsCheckoutException::UNKNOWN);
        } else {
            $this->expectException(TypeError::class);
        }

        new LoggerFactory($data['name'], $data['loggerHandler']);
    }

    /**
     * @return Generator
     */
    public function getValidValue()
    {
        yield [[
            'name' => 'ps_checkout',
            'loggerHandler' => $this->createMock(HandlerInterface::class),
        ]];
    }

    /**
     * @return Generator
     */
    public function getInvalidValue()
    {
        yield [[
            'name' => null,
            'loggerHandler' => null,
        ]];
        yield [[
            'name' => '<ps_checkout>',
            'loggerHandler' => null,
        ]];
        yield [[
            'name' => '{ps_checkout}',
            'loggerHandler' => null,
        ]];
        yield [[
            'name' => null,
            'loggerHandler' => $this->createMock(HandlerInterface::class),
        ]];
    }
}
