<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
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
     * @var \Module
     */
    private $module;

    /**
     * @var CacheDirectory
     */
    private $cacheDirectory;

    /**
     * @param \Module $module
     * @param CacheDirectory $cacheDirectory
     */
    public function __construct(\Module $module, CacheDirectory $cacheDirectory)
    {
        $this->module = $module;
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * @param string $containerName
     *
     * @return ContainerInterface
     */
    public function get($containerName)
    {
        $containerClassName = ucfirst($this->module->name)
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
            $this->module->name . '.cache.directory',
            $this->cacheDirectory
        );
        $moduleConfigPath = $this->module->getLocalPath()
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
