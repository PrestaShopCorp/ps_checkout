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

namespace Tests\Unit\PayPal\ApplePay;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PayPal\ApplePay\AppleSetup;
use PrestaShop\Module\PrestashopCheckout\PayPal\ApplePay\Exception\ApplePaySetupException;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\System\SystemConfiguration;

class AppleSetupTest extends TestCase
{
    private $systemConfigurationMock;
    private $payPalConfigurationMock;

    protected function setUp(): void
    {
        $this->systemConfigurationMock = $this->createMock(SystemConfiguration::class);
        $this->payPalConfigurationMock = $this->createMock(PayPalConfiguration::class);
    }

    public function testSetupInvokesRegisterModuleRoutesHookWhenApacheServerAndWellKnownFileNotExist()
    {
        $this->systemConfigurationMock->method('isApacheServer')->willReturn(true);
        $appleSetupMock = $this->getMockBuilder(AppleSetup::class)
            ->setConstructorArgs([$this->systemConfigurationMock, $this->payPalConfigurationMock])
            ->setMethods(['checkWellKnownFileExist', 'registerModuleRoutesHook'])
            ->getMock();

        $appleSetupMock->method('checkWellKnownFileExist')->willReturn(false);
        $appleSetupMock->expects($this->once())->method('registerModuleRoutesHook');

        $appleSetupMock->setup();
    }

    public function testSetupInvokesCopyWellKnownFileWhenApacheServerAndWellKnownFileExist()
    {
        $this->systemConfigurationMock->method('isApacheServer')->willReturn(true);
        $appleSetupMock = $this->getMockBuilder(AppleSetup::class)
            ->setConstructorArgs([$this->systemConfigurationMock, $this->payPalConfigurationMock])
            ->setMethods(['checkWellKnownFileExist', 'copyWellKnownFile'])
            ->getMock();

        $appleSetupMock->method('checkWellKnownFileExist')->willReturn(true);
        $appleSetupMock->expects($this->once())->method('copyWellKnownFile');

        $appleSetupMock->setup();
    }

    public function testSetupInvokesCopyWellKnownFileWhenNotApacheServer()
    {
        $this->systemConfigurationMock->method('isApacheServer')->willReturn(false);
        $appleSetupMock = $this->getMockBuilder(AppleSetup::class)
            ->setConstructorArgs([$this->systemConfigurationMock, $this->payPalConfigurationMock])
            ->setMethods(['checkWellKnownFileExist', 'copyWellKnownFile'])
            ->getMock();

        $appleSetupMock->method('checkWellKnownFileExist')->willReturn(false);
        $appleSetupMock->expects($this->once())->method('copyWellKnownFile');

        $appleSetupMock->setup();
    }

    public function testSetupThrowsExceptionWhenRegisterModuleRoutesHookFails()
    {
        $this->systemConfigurationMock->method('isApacheServer')->willReturn(true);
        $appleSetupMock = $this->getMockBuilder(AppleSetup::class)
            ->setConstructorArgs([$this->systemConfigurationMock, $this->payPalConfigurationMock])
            ->setMethods(['checkWellKnownFileExist', 'registerModuleRoutesHook'])
            ->getMock();

        $appleSetupMock->method('checkWellKnownFileExist')->willReturn(false);
        $appleSetupMock->method('registerModuleRoutesHook')->will($this->throwException(new ApplePaySetupException('Failed to register moduleRoutes hook for ps_checkout.', ApplePaySetupException::FAILED_REGISTER_HOOK)));

        $this->expectException(ApplePaySetupException::class);
        $this->expectExceptionMessage('Failed to register moduleRoutes hook for ps_checkout.');
        $appleSetupMock->setup();
    }

    public function testSetupThrowsExceptionWhenPrestaShopRootDirNotFound()
    {
        $this->systemConfigurationMock->method('isApacheServer')->willReturn(false);
        $appleSetupMock = $this->getMockBuilder(AppleSetup::class)
            ->setConstructorArgs([$this->systemConfigurationMock, $this->payPalConfigurationMock])
            ->setMethods(['getPrestaShopRootDir'])
            ->getMock();

        $appleSetupMock->method('getPrestaShopRootDir')->will($this->throwException(new ApplePaySetupException('Unable to retrieve the PrestaShop Root directory path.', ApplePaySetupException::UNABLE_RETRIEVE_ROOT_DIR)));

        $this->expectException(ApplePaySetupException::class);
        $this->expectExceptionMessage('Unable to retrieve the PrestaShop Root directory path.');
        $appleSetupMock->setup();
    }

    public function testSetupThrowsExceptionWhenPrestaShopNotAtDomainRoot()
    {
        $this->systemConfigurationMock->method('isApacheServer')->willReturn(false);
        $appleSetupMock = $this->getMockBuilder(AppleSetup::class)
            ->setConstructorArgs([$this->systemConfigurationMock, $this->payPalConfigurationMock])
            ->setMethods(['checkPrestaShopIsAtDomainRoot', 'getPrestaShopRootDir'])
            ->getMock();

        $appleSetupMock->method('getPrestaShopRootDir')->willReturn('/path/to/prestashop/root');
        $appleSetupMock->method('checkPrestaShopIsAtDomainRoot')->will($this->throwException(new ApplePaySetupException('PrestaShop is not installed at the domain root.', ApplePaySetupException::PRESTASHOP_NOT_AT_DOMAIN_ROOT)));

        $this->expectException(ApplePaySetupException::class);
        $this->expectExceptionMessage('PrestaShop is not installed at the domain root.');
        $appleSetupMock->setup();
    }

    public function testSetupThrowsExceptionWhenWellKnownDirNotWritable()
    {
        $this->systemConfigurationMock->method('isApacheServer')->willReturn(false);
        $appleSetupMock = $this->getMockBuilder(AppleSetup::class)
            ->setConstructorArgs([$this->systemConfigurationMock, $this->payPalConfigurationMock])
            ->setMethods(['getWellKnownDir', 'getPrestaShopRootDir', 'checkPrestaShopIsAtDomainRoot'])
            ->getMock();

        $appleSetupMock->method('getPrestaShopRootDir')->willReturn('/path/to/prestashop/root');
        $appleSetupMock->method('checkPrestaShopIsAtDomainRoot')->willReturn(true);
        $appleSetupMock->method('getWellKnownDir')->will($this->throwException(new ApplePaySetupException('The ".well-known" directory is not writable in the PrestaShop root directory.', ApplePaySetupException::WELL_KNOWN_DIR_NOT_WRITABLE)));

        $this->expectException(ApplePaySetupException::class);
        $this->expectExceptionMessage('The ".well-known" directory is not writable in the PrestaShop root directory.');
        $appleSetupMock->setup();
    }

    public function testSetupThrowsExceptionWhenAppleDomainFileNotWritable()
    {
        $this->systemConfigurationMock->method('isApacheServer')->willReturn(false);
        $appleSetupMock = $this->getMockBuilder(AppleSetup::class)
            ->setConstructorArgs([$this->systemConfigurationMock, $this->payPalConfigurationMock])
            ->setMethods(['getDestinationFile', 'copyWellKnownFile'])
            ->getMock();

        $appleSetupMock->method('getDestinationFile')->willReturn('/path/to/destination/file');
        $appleSetupMock->method('copyWellKnownFile')->will($this->throwException(new ApplePaySetupException('The Apple Domain Association file is not writable in the PrestaShop root directory.', ApplePaySetupException::APPLE_DOMAIN_FILE_NOT_WRITABLE)));

        $this->expectException(ApplePaySetupException::class);
        $this->expectExceptionMessage('The Apple Domain Association file is not writable in the PrestaShop root directory.');
        $appleSetupMock->setup();
    }

    public function testSetupThrowsExceptionWhenAppleDomainFileNotFound()
    {
        $this->systemConfigurationMock->method('isApacheServer')->willReturn(false);
        $appleSetupMock = $this->getMockBuilder(AppleSetup::class)
            ->setConstructorArgs([$this->systemConfigurationMock, $this->payPalConfigurationMock])
            ->setMethods(['getSourceFile', 'getPrestaShopRootDir', 'checkPrestaShopIsAtDomainRoot', 'createDir', 'isWritable'])
            ->getMock();

        $appleSetupMock->method('getPrestaShopRootDir')->willReturn('/path/to/prestashop/root');
        $appleSetupMock->method('checkPrestaShopIsAtDomainRoot')->willReturn(true);
        $appleSetupMock->method('createDir')->willReturn(true);
        $appleSetupMock->method('isWritable')->willReturn(true);
        $appleSetupMock->method('getSourceFile')->will($this->throwException(new ApplePaySetupException('The Apple Domain Association file could not be found in the module directory.', ApplePaySetupException::APPLE_DOMAIN_FILE_NOT_FOUND)));

        $this->expectException(ApplePaySetupException::class);
        $this->expectExceptionMessage('The Apple Domain Association file could not be found in the module directory.');
        $appleSetupMock->setup();
    }
}
