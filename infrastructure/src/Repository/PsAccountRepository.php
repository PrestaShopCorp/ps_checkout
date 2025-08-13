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

namespace PsCheckout\Infrastructure\Repository;

use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException;
use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;

/**
 * Repository for PsAccount class
 */
class PsAccountRepository implements PsAccountRepositoryInterface
{
    private $psAccountsService;

    /**
     * @param PsAccounts $psAccountsFacade
     */
    public function __construct(PsAccounts $psAccountsFacade)
    {
        try {
            $this->psAccountsService = $psAccountsFacade->getPsAccountsService();
        } catch (ModuleNotInstalledException $exception) {
            $this->psAccountsService = false;
        } catch (ModuleVersionException $exception) {
            $this->psAccountsService = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdToken()
    {
        if (!$this->psAccountsService) {
            return false;
        }

        return $this->psAccountsService->getOrRefreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getShopUuid()
    {
        if (!$this->psAccountsService) {
            return false;
        }

        return $this->psAccountsService->getShopUuid();
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountLinked(): bool
    {
        if (!$this->psAccountsService || !method_exists($this->psAccountsService, 'isAccountLinked')) {
            return false;
        }

        return $this->psAccountsService->isAccountLinked();
    }
}
