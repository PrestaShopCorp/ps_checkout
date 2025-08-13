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

use PsCheckout\Core\Settings\Configuration\DefaultConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Repository\ShopRepositoryInterface;

class ConfigurationInstaller implements InstallerInterface
{
    /**
     * @var ShopRepositoryInterface
     */
    private $shop;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ShopRepositoryInterface $shop, ConfigurationInterface $configuration)
    {
        $this->shop = $shop;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function init(): bool
    {
        $result = true;

        foreach ($this->shop->getAll() as $shopId) {
            foreach (DefaultConfiguration::DEFAULT_CONFIGURATION_VALUES as $name => $value) {
                if (!$this->configuration->getForSpecificShop($name, (int) $shopId)) {
                    $result = $result && $this->configuration->setForSpecificShop(
                        $name,
                        $value,
                        (int) $shopId
                    );
                }
            }
        }

        return $result;
    }
}
