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

namespace PsCheckout\Infrastructure\Bootstrap\Install;

use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\SystemConfigurationInterface;

class ApplePayInstaller
{
    /**
     * @var SystemConfigurationInterface
     */
    private $systemConfiguration;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(SystemConfigurationInterface $systemConfiguration, ConfigurationInterface $configuration)
    {
        $this->systemConfiguration = $systemConfiguration;
        $this->configuration = $configuration;
    }

    /**
     * @return void
     *
     * @throws ApplePayInstallerException
     */
    public function setup()
    {
        if ($this->systemConfiguration->isApacheServer() && !$this->checkWellKnownFileExist()) {
            $this->registerModuleRoutesHook();
        } else {
            $this->copyWellKnownFile();
        }
    }

    /**
     * @return void
     *
     * @throws ApplePayInstallerException
     */
    public function registerModuleRoutesHook()
    {
        try {
            $module = \Module::getInstanceByName('ps_checkout');
            $shopList = \Shop::getCompleteListOfShopsID();
            if (!\Hook::registerHook($module, 'moduleRoutes', $shopList)) {
                throw new ApplePayInstallerException('Failed to register moduleRoutes hook for ps_checkout.', ApplePayInstallerException::FAILED_REGISTER_HOOK);
            }
        } catch (\Exception $e) {
            throw new ApplePayInstallerException('Failed to register moduleRoutes hook for ps_checkout.', ApplePayInstallerException::ERROR_REGISTER_HOOK, $e);
        }
    }

    /**
     * @param string $wellKnownDir
     *
     * @return string
     */
    public function getDestinationFile($wellKnownDir)
    {
        return $wellKnownDir . '/apple-developer-merchantid-domain-association';
    }

    /**
     * @return bool
     *
     * @throws ApplePayInstallerException
     */
    public function checkWellKnownFileExist()
    {
        $rootDir = $this->getPrestaShopRootDir();
        $wellKnownDir = $this->getWellKnownDir($rootDir);
        $destinationFile = $this->getDestinationFile($wellKnownDir);

        return file_exists($destinationFile);
    }

    /**
     * @return void
     *
     * @throws ApplePayInstallerException
     */
    public function copyWellKnownFile()
    {
        $rootDir = $this->getPrestaShopRootDir();
        $this->checkPrestaShopIsAtDomainRoot();
        $wellKnownDir = $this->getWellKnownDir($rootDir);
        $sourceFile = $this->getSourceFile();
        $destinationFile = $this->getDestinationFile($wellKnownDir);

        if (file_exists($destinationFile) && !$this->isWritable($destinationFile)) {
            throw new ApplePayInstallerException('The Apple Domain Association file is not writable in the PrestaShop root directory.', ApplePayInstallerException::APPLE_DOMAIN_FILE_NOT_WRITABLE);
        }

        if (!copy($sourceFile, $destinationFile)) {
            throw new ApplePayInstallerException('Failed to copy the "apple-developer-merchantid-domain-association" file to the PrestaShop root directory.', ApplePayInstallerException::FAILED_COPY_APPLE_DOMAIN_FILE);
        }
    }

    /**
     * @return string
     *
     * @throws ApplePayInstallerException
     */
    public function getPrestaShopRootDir()
    {
        $rootDir = defined('_PS_ROOT_DIR_') ? constant('_PS_ROOT_DIR_') : null;

        if (!$rootDir || !is_dir($rootDir)) {
            throw new ApplePayInstallerException('Unable to retrieve the PrestaShop Root directory path.', ApplePayInstallerException::UNABLE_RETRIEVE_ROOT_DIR);
        }

        return $rootDir;
    }

    /**
     * @return void
     *
     * @throws ApplePayInstallerException
     */
    public function checkPrestaShopIsAtDomainRoot()
    {
        $defaultShop = new \Shop((int) \Configuration::get('PS_SHOP_DEFAULT'));

        if (!$defaultShop->physical_uri) {
            throw new ApplePayInstallerException('Unable to retrieve the base URI of the shop.', ApplePayInstallerException::UNABLE_RETRIEVE_BASE_URI);
        }

        if ($defaultShop->physical_uri !== '/') {
            throw new ApplePayInstallerException('PrestaShop is not installed at the domain root.', ApplePayInstallerException::PRESTASHOP_NOT_AT_DOMAIN_ROOT);
        }
    }

    /**
     * @param string $rootDir
     *
     * @return string
     *
     * @throws ApplePayInstallerException
     */
    public function getWellKnownDir($rootDir)
    {
        $wellKnownDir = $rootDir . '/.well-known';

        if (!is_dir($wellKnownDir)) {
            if (!$this->createDir($wellKnownDir)) {
                throw new ApplePayInstallerException('Failed to create the ".well-known" directory in the PrestaShop root directory.', ApplePayInstallerException::FAILED_CREATE_WELL_KNOWN_DIR);
            }
        }

        if (!$this->isWritable($wellKnownDir)) {
            throw new ApplePayInstallerException('The ".well-known" directory is not writable in the PrestaShop root directory.', ApplePayInstallerException::WELL_KNOWN_DIR_NOT_WRITABLE);
        }

        return $wellKnownDir;
    }

    /**
     * @return string
     *
     * @throws ApplePayInstallerException
     */
    public function getSourceFile()
    {
        $moduleDir = defined('_PS_MODULE_DIR_') ? constant('_PS_MODULE_DIR_') : null;
        $moduleWellKnownDir = $moduleDir . 'ps_checkout/.well-known';
        $paypalEnvironment = $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYMENT_MODE);
        $sourceFile = "$moduleWellKnownDir/apple-$paypalEnvironment-merchantid-domain-association";

        if (!file_exists($sourceFile)) {
            throw new ApplePayInstallerException('The Apple Domain Association file could not be found in the module directory.', ApplePayInstallerException::APPLE_DOMAIN_FILE_NOT_FOUND);
        }

        return $sourceFile;
    }

    /**
     * @param string $wellKnownDir
     *
     * @return bool
     */
    public function createDir($wellKnownDir)
    {
        return mkdir($wellKnownDir, 0755, true);
    }

    /**
     * @param string $wellKnownDir
     *
     * @return bool
     */
    public function isWritable($wellKnownDir)
    {
        return is_writable($wellKnownDir);
    }
}
