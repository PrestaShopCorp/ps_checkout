<?php

namespace PrestaShop\Module\PrestashopCheckout\OnBoarding\Helper;

use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PsAccounts\Service\PsAccountsService;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;


class OnBoardingStatusHelper implements PresenterInterface
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

    public function isPsCheckoutOnboarded()
    {
        return !empty((new Token())->getToken());
    }

    public function isPsAccountsOnboarded()
    {
        try {
            $psAccountsService = $this->psAccountsFacade->getPsAccountsService();
            return $psAccountsService->isAccountLinked();
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function present()
    {
        return [
            'onboarding' => [
                'isPsAccountsOnboarded' => $this->isPsAccountsOnboarded(),
                'isPsCheckoutOnboarded' => $this->isPsCheckoutOnboarded(),
            ]
        ];
    }
}
