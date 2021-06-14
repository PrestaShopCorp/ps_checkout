<?php

namespace PrestaShop\Module\PrestashopCheckout\OnBoarding\Helper;

use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PsAccounts\Service\PsAccountsService;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;

class OnBoardingStatusHelper
{
    /** @var PrestaShopConfiguration */
    private $configuration;
    /**
     * @var PsAccountsService
     */
    private $psAccountsService;

    /**
     * @param PrestaShopConfiguration $configuration
     */
    public function __construct(PrestaShopConfiguration $configuration, PsAccounts $psAccountsFacade)
    {
        $this->configuration = $configuration;
        $this->psAccountsService = $psAccountsFacade->getPsAccountsService();
    }

    public function isPsCheckoutOnboarded()
    {
        return !empty((new Token())->getToken());
    }

    public function isPsAccountsOnboarded()
    {
        return $this->psAccountsService->isAccountLinked();
    }
}
