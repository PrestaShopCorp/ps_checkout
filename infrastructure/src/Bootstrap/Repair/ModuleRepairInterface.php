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

interface ModuleRepairInterface
{
    /**
     * Checks if the module installation is incomplete: a declared admin tab is missing
     * or the module is not registered on any hook.
     *
     * @return bool
     */
    public function isRepairNeeded(): bool;

    /**
     * Registers missing admin tabs, runs the installer again (it never overrides existing data)
     * and registers the given hooks.
     *
     * @param string[] $hooks Hooks the module must be registered on
     *
     * @return bool
     */
    public function repair(array $hooks): bool;
}
