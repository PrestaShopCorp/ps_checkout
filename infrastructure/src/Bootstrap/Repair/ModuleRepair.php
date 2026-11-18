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

namespace PsCheckout\Infrastructure\Bootstrap\Repair;

use PsCheckout\Infrastructure\Adapter\ShopContextInterface;
use PsCheckout\Infrastructure\Bootstrap\Install\InstallerInterface;
use Psr\Log\LoggerInterface;

/**
 * Detects and repairs an incomplete module installation.
 *
 * PrestaShop resets a module by uninstalling then installing it. When the install step fails,
 * the module stays flagged as installed but its admin tabs and hooks are gone, which makes
 * the configuration page unusable.
 */
class ModuleRepair implements ModuleRepairInterface
{
    /**
     * @var \Module
     */
    private $module;

    /**
     * @var \Db
     */
    private $db;

    /**
     * @var InstallerInterface
     */
    private $installer;

    /**
     * @var ShopContextInterface
     */
    private $shopContext;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        \Module $module,
        \Db $db,
        InstallerInterface $installer,
        ShopContextInterface $shopContext,
        LoggerInterface $logger
    ) {
        $this->module = $module;
        $this->db = $db;
        $this->installer = $installer;
        $this->shopContext = $shopContext;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function isRepairNeeded(): bool
    {
        return !empty($this->getMissingTabs()) || !$this->hasRegisteredHooks();
    }

    /**
     * {@inheritdoc}
     */
    public function repair(array $hooks): bool
    {
        $currentShopContext = $this->shopContext->getCurrent();
        $this->shopContext->setAllShopContext();

        try {
            // Every step runs even if a previous one failed, to restore as much as possible
            $tabsRegistered = $this->runStep('admin tabs registration', function () {
                return $this->registerMissingTabs();
            });
            $installerExecuted = $this->runStep('installer execution', function () {
                return $this->installer->init();
            });
            $hooksRegistered = $this->runStep('hooks registration', function () use ($hooks) {
                return (bool) $this->module->registerHook($hooks);
            });
        } finally {
            $this->shopContext->setContext($currentShopContext);
        }

        $result = $tabsRegistered && $installerExecuted && $hooksRegistered;

        $this->logger->info('Module repair finished', [
            'success' => $result,
            'tabsRegistered' => $tabsRegistered,
            'installerExecuted' => $installerExecuted,
            'hooksRegistered' => $hooksRegistered,
        ]);

        return $result;
    }

    /**
     * @param string $name
     * @param callable $step
     *
     * @return bool
     */
    private function runStep(string $name, callable $step): bool
    {
        try {
            $result = (bool) $step();
        } catch (\Throwable $exception) {
            $this->logger->error('Module repair failed: ' . $name, [
                'exception' => get_class($exception),
                'exceptionCode' => $exception->getCode(),
                'exceptionMessage' => $exception->getMessage(),
            ]);

            return false;
        }

        if (!$result) {
            $this->logger->error('Module repair failed: ' . $name);
        }

        return $result;
    }

    /**
     * @return array<int, array{class_name: string, visible: bool}> Tabs declared by the module that are not in database
     */
    private function getMissingTabs(): array
    {
        $missingTabs = [];

        foreach ($this->module->getTabs() as $tab) {
            if (!is_array($tab) || empty($tab['class_name']) || !is_string($tab['class_name'])) {
                continue;
            }

            $tabId = $this->db->getValue(
                'SELECT `id_tab` FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` = "' . $this->db->escape($tab['class_name']) . '"'
            );

            if (!$tabId) {
                $missingTabs[] = [
                    'class_name' => $tab['class_name'],
                    'visible' => !isset($tab['visible']) || $tab['visible'],
                ];
            }
        }

        return $missingTabs;
    }

    /**
     * A failed installation leaves the module without any hook, hooks unregistered
     * on purpose by the merchant must not be seen as a broken installation.
     *
     * @return bool
     */
    private function hasRegisteredHooks(): bool
    {
        return (int) $this->db->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'hook_module` WHERE `id_module` = ' . (int) $this->module->id
        ) > 0;
    }

    /**
     * Same behavior as PrestaShop ModuleTabRegister, which only runs on a successful installation.
     *
     * @return bool
     */
    private function registerMissingTabs(): bool
    {
        $result = true;

        foreach ($this->getMissingTabs() as $tabDetails) {
            $names = [];

            foreach (\Language::getIDs(false) as $languageId) {
                if (is_numeric($languageId)) {
                    $names[(int) $languageId] = $tabDetails['class_name'];
                }
            }

            $tab = new \Tab();
            $tab->class_name = $tabDetails['class_name'];
            $tab->module = $this->module->name;
            $tab->active = $tabDetails['visible'];
            $tab->enabled = true;
            $tab->id_parent = 0;
            $tab->name = $names;

            $result = $tab->save() && $result;
        }

        return $result;
    }
}
