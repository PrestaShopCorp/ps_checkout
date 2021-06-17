<?php

namespace PrestaShop\Module\PrestashopCheckout\OnBoarding\Helper;

use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Entity\PsAccount;
use PrestaShop\Module\PsAccounts\Service\PsAccountsService;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;


class OnBoardingStatusHelper
{
    /** @var PrestaShopConfiguration */
    private $configuration;
    /**
     * @var PsAccounts
     */
    private $psAccountsFacade;

    /**
     * @param PrestaShopConfiguration $configuration
     */
    public function __construct(PrestaShopConfiguration $configuration, PsAccounts $psAccountsFacade)
    {
        $this->configuration = $configuration;
        $this->psAccountsFacade = $psAccountsFacade;
    }

    /**
     * @return bool
     *
     * Did not use (new Token)->getToken() because it would create a circular dependency
     */
    public function isPsCheckoutOnboarded()
    {
        return !empty($this->configuration->get(PsAccount::PS_ACCOUNTS_FIREBASE_ID_TOKEN));
    }

    /**
     * @return false
     */
    public function isPsAccountsOnboarded()
    {
        try {
            $psAccountsService = $this->psAccountsFacade->getPsAccountsService();
            return $psAccountsService->isAccountLinked();
        } catch (\Exception $exception) {
            return false;
        }
    }
}
