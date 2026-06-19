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

class HookInstaller implements InstallerInterface
{
    /**
     * Custom hooks dispatched by ps_checkout that third-party modules may implement.
     *
     * @var array<int, array{name: string, title: string, description: string}>
     */
    const HOOKS = [
        [
            'name' => 'actionGetPsCheckoutCarrierType',
            'title' => 'Get ps_checkout carrier type',
            'description' => 'Allows external modules to override a carrier type (SHIPPING or PICKUP) and disabled state for the PayPal shipping overlay. Receives id_carrier, id_reference (0 when carrier has no pscheckout_carrier row), type (by ref), disabled (by ref).',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function init(): bool
    {
        foreach (self::HOOKS as $hookData) {
            if (\Hook::getIdByName($hookData['name'])) {
                continue;
            }

            $hook = new \Hook();
            $hook->name = $hookData['name'];
            $hook->title = $hookData['title'];
            $hook->description = $hookData['description'];
            $hook->position = 1;

            if (!$hook->add()) {
                return false;
            }
        }

        return true;
    }
}
