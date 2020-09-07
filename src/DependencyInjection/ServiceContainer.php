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

namespace PrestaShop\Module\PrestashopCheckout\DependencyInjection;

use PrestaShop\Module\PrestashopCheckout\Cache\CacheDirectory;

class ServiceContainer
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var string
     */
    private $moduleLocalPath;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @param \Module $module
     */
    public function __construct($moduleName, $moduleLocalPath)
    {
        $this->moduleName = $moduleName;
        $this->moduleLocalPath = $moduleLocalPath;
    }

    public function getService($serviceName)
    {
        if (null === $this->container) {
            $this->initContainer();
        }

        return $this->container->get($serviceName);
    }

    private function initContainer()
    {
        $cacheDirectory = new CacheDirectory(
            _PS_VERSION_,
            _PS_ROOT_DIR_,
            _PS_MODE_DEV_
        );
        $containerProvider = new ContainerProvider($this->moduleName, $this->moduleLocalPath, $cacheDirectory);

        $this->container = $containerProvider->get(defined('_PS_ADMIN_DIR_') ? 'admin' : 'front');
    }
}
