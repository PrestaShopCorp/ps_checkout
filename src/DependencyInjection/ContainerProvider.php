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
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContainerProvider
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
     * @var CacheDirectory
     */
    private $cacheDirectory;

    /**
     * @param string $moduleName
     * @param string $moduleLocalPath
     * @param CacheDirectory $cacheDirectory
     */
    public function __construct($moduleName, $moduleLocalPath, CacheDirectory $cacheDirectory)
    {
        $this->moduleName = $moduleName;
        $this->moduleLocalPath = $moduleLocalPath;
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * @param string $containerName
     *
     * @return ContainerInterface
     */
    public function get($containerName)
    {
        $containerClassName = ucfirst($this->moduleName)
            . ucfirst($containerName)
            . 'Container'
        ;
        $containerFilePath = $this->cacheDirectory->getPath() . '/' . $containerClassName . '.php';
        $containerConfigCache = new ConfigCache($containerFilePath, _PS_MODE_DEV_);

        if ($containerConfigCache->isFresh()) {
            require_once $containerFilePath;

            return new $containerClassName();
        }

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->set(
            $this->moduleName . '.cache.directory',
            $this->cacheDirectory
        );
        $moduleConfigPath = $this->moduleLocalPath
            . 'config/'
            . $containerName
        ;
        $loader = new YamlFileLoader($containerBuilder, new FileLocator($moduleConfigPath));
        $loader->load('services.yml');
        $containerBuilder->compile();
        $dumper = new PhpDumper($containerBuilder);
        $containerConfigCache->write(
            $dumper->dump(['class' => $containerClassName]),
            $containerBuilder->getResources()
        );

        return $containerBuilder;
    }
}
