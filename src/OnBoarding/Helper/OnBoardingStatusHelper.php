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

namespace PrestaShop\Module\PrestashopCheckout\OnBoarding\Helper;

use Exception;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Entity\PsAccount;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;

class OnBoardingStatusHelper
{
    /** @var PrestaShopConfiguration */
    private $configuration;
    /**
     * @var PsAccounts
     */
    private $psAccountsFacade;
    /**
     * @var PaypalAccountRepository
     */
    private $paypalAccountRepository;

    /**
     * @param PrestaShopConfiguration $configuration
     * @param PsAccounts $psAccountsFacade
     * @param PaypalAccountRepository $paypalAccountRepository
     */
    public function __construct(
        PrestaShopConfiguration $configuration,
        PsAccounts $psAccountsFacade,
        PaypalAccountRepository $paypalAccountRepository
    ) {
        $this->configuration = $configuration;
        $this->psAccountsFacade = $psAccountsFacade;
        $this->paypalAccountRepository = $paypalAccountRepository;
    }

    /**
     * @return bool
     *
     * Did not use (new Token)->getToken() because it would create a circular dependency
     */
    public function isPsCheckoutOnboarded()
    {
        return !empty($this->configuration->get(PsAccount::PS_PSX_FIREBASE_ID_TOKEN));
    }

    /**
     * @return bool
     */
    public function isPsAccountsOnboarded()
    {
        try {
            /** @var PrestaShop\Module\PsAccounts\Service\PsAccountsService $psAccountsService */
            $psAccountsService = $this->psAccountsFacade->getPsAccountsService();

            return $psAccountsService->isAccountLinked();
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isPayPalOnboarded()
    {
        return $this->paypalAccountRepository->onBoardingIsCompleted();
    }

    /**
     * @return bool
     */
    public function isPsCheckoutLoginAllowed()
    {
        return (int) $this->configuration->get(PsAccount::ALLOW_PS_CHECKOUT_LOGIN) == 1;
    }

    /**
     * @return bool
     */
    public function isFullyOnboarded()
    {
        return $this->isPayPalOnboarded() && ($this->isPsCheckoutOnboarded() || $this->isPsAccountsOnboarded());
    }
}
