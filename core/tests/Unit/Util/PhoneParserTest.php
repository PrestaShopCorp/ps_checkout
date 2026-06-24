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

namespace Tests\Unit\PsCheckout\Core\Util;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Util\PhoneParser;
use Psr\Log\LoggerInterface;

class PhoneParserTest extends TestCase
{
    private function makeParser(?LoggerInterface $logger = null): PhoneParser
    {
        return new PhoneParser($logger ?? $this->createMock(LoggerInterface::class));
    }

    private function makeAddress(string $phone = '', string $phoneMobile = ''): \stdClass
    {
        $address = new \stdClass();
        $address->phone = $phone;
        $address->phone_mobile = $phoneMobile;
        $address->id = 1;

        return $address;
    }

    public function testParsePhoneReturnsPhoneNumber(): void
    {
        $result = $this->makeParser()->parsePhone('+33612345678', 'FR');

        $this->assertNotNull($result);
        $this->assertSame('33', (string) $result->getCountryCode());
    }

    public function testParsePhoneReturnsNullAndLogsOnUnparseablePhone(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        $result = $this->makeParser($logger)->parsePhone('not-a-phone', 'FR');

        $this->assertNull($result);
    }

    public function testParsePhoneReturnsNullForInvalidNumber(): void
    {
        $result = $this->makeParser()->parsePhone('00000', 'FR');

        $this->assertNull($result);
    }

    public function testParsePhoneMergesLogContext(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->isType('string'),
                $this->callback(static function (array $ctx) {
                    return $ctx['id_cart'] === 42 && $ctx['phone'] === 'not-a-phone';
                })
            );

        $this->makeParser($logger)->parsePhone('not-a-phone', 'FR', ['id_cart' => 42]);
    }

    public function testParseFromAddressReturnsPhoneNumber(): void
    {
        $result = $this->makeParser()->parseFromAddress($this->makeAddress('+33612345678'), 'FR');

        $this->assertNotNull($result);
        $this->assertSame('33', (string) $result->getCountryCode());
    }

    public function testParseFromAddressFallsBackToPhoneMobile(): void
    {
        $result = $this->makeParser()->parseFromAddress($this->makeAddress('', '+33612345678'), 'FR');

        $this->assertNotNull($result);
    }

    public function testParseFromAddressReturnsNullWhenBothPhonesEmpty(): void
    {
        $result = $this->makeParser()->parseFromAddress($this->makeAddress(), 'FR');

        $this->assertNull($result);
    }

    public function testParseFromAddressPassesCartAndAddressIdToLogContext(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->isType('string'),
                $this->callback(static function (array $ctx) {
                    return $ctx['id_cart'] === 42 && $ctx['address_id'] === 1;
                })
            );

        $this->makeParser($logger)->parseFromAddress($this->makeAddress('not-a-phone'), 'FR', 42);
    }

    public function testGetPhoneTypeReturnsMobileForMobileNumber(): void
    {
        $parser = $this->makeParser();
        $phone = $parser->parseFromAddress($this->makeAddress('+33612345678'), 'FR');

        $this->assertSame('MOBILE', $parser->getPhoneType($phone));
    }

    public function testGetPhoneTypeReturnsOtherForLandline(): void
    {
        $parser = $this->makeParser();
        $phone = $parser->parseFromAddress($this->makeAddress('+33140000000'), 'FR');

        $this->assertNotNull($phone);
        $this->assertSame('OTHER', $parser->getPhoneType($phone));
    }
}
