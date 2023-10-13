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

namespace PrestaShop\Module\PrestashopCheckout\Repository;

use Exception;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Entity\PsAccount;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;

/**
 * Repository for PsAccount class
 */
class PsAccountRepository
{
    /** @var PrestaShopConfiguration */
    private $configuration;

    private $psAccountsService;

    /**
     * @param PrestaShopConfiguration $configuration
     * @param PsAccounts $psAccountsFacade
     */
    public function __construct(PrestaShopConfiguration $configuration, PsAccounts $psAccountsFacade)
    {
        $this->configuration = $configuration;
        try {
            $this->psAccountsService = $psAccountsFacade->getPsAccountsService();
        } catch (Exception $exception) {
            $this->psAccountsService = false;
        }
    }

    /**
     * Get current onboarded prestashop account
     *
     * @return PsAccount
     */
    public function getOnboardedAccount()
    {
        return new PsAccount(
            $this->getIdToken(),
            $this->getRefreshToken(),
            $this->getEmail()
        );
    }

    /**
     * @deprecated 3.0.0 Moved to PS Accounts
     *
     * @return bool
     */
    public function psxFormIsCompleted()
    {
        return true;
    }

    /**
     * Check if user and shop are linked with PS Accounts
     *
     * @return bool
     */
    public function onBoardingIsCompleted()
    {
        return $this->isAccountLinked();
    }

    /**
     * Get firebase email from database
     *
     * @return string|bool
     */
    public function getEmail()
    {
        if (!$this->psAccountsService) {
            return false;
        }

        return $this->psAccountsService->getEmail();
    }

    /**
     * Get firebase idToken from database
     *
     * @return string|bool
     */
    public function getIdToken()
    {
        if (!$this->psAccountsService) {
            return false;
        }

        try {
            return (string) $this->psAccountsService->getOrRefreshToken();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @deprecated PS Accounts v5.1.1
     *
     * @return string|bool
     */
    public function getLocalId()
    {
        if (!$this->psAccountsService) {
            return false;
        }

        return $this->psAccountsService->getUserUuidV4();
    }

    /**
     * Get firebase refreshToken from database
     *
     * @return string|bool
     */
    public function getRefreshToken()
    {
        if (!$this->psAccountsService) {
            return false;
        }

        return $this->psAccountsService->getRefreshToken();
    }

    /**
     * Get psx form from database
     *
     * @param bool $toArray
     *
     * @return string|array
     */
    public function getPsxForm($toArray = false)
    {
        return $toArray ? '' : [];
    }

    /**
     * Get Shop UUID
     *
     * @return string|bool
     */
    public function getShopUuid()
    {
        if (!$this->psAccountsService) {
            return false;
        }

        return $this->psAccountsService->getShopUuid();
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function isEmailValidated()
    {
        if (!$this->psAccountsService) {
            return false;
        }

        return $this->psAccountsService->isEmailValidated();
    }

    /**
     * @return bool
     */
    public function isAccountLinked()
    {
        if (!$this->psAccountsService || !method_exists($this->psAccountsService, 'isAccountLinked')) {
            return false;
        }

        try {
            return $this->psAccountsService->isAccountLinked();
        } catch (Exception $e) {
            return false;
        }
    }
}
