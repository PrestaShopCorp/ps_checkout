<?php

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\OnBoarding\Helper\OnBoardingStatusHelper;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\PsAccountsInstaller\Installer\Installer;

class OnboardingModule implements PresenterInterface
{
    /**
     * @var OnBoardingStatusHelper
     */
    private $onBoardingStatusHelper;
    /**
     * @var PrestaShopContext
     */
    private $prestaShopContext;
    /**
     * @var Installer
     */
    private $installer;

    public function __construct(
        OnBoardingStatusHelper $onBoardingStatusHelper,
        PrestaShopContext $prestaShopContext,
        Installer $installer
    ) {
        $this->onBoardingStatusHelper = $onBoardingStatusHelper;
        $this->prestaShopContext = $prestaShopContext;
        $this->installer = $installer;
    }

    public function present()
    {
        return [
            'onboarding' => [
                'psAccountsOnboarded' => $this->onBoardingStatusHelper->isPsAccountsOnboarded(),
                'psCheckoutOnboarded' => $this->onBoardingStatusHelper->isPsCheckoutOnboarded(),
                'psAccountsEnabled' => $this->installer->isModuleEnabled(),
                'psAccountsConfigureURL' => $this->prestaShopContext->getLink()->getAdminLink(
                    'adminModules',
                    true,
                    [
                        'configure' => 'ps_accounts',
                    ],
                    [
                        'configure' => 'ps_accounts',
                    ]
                ),
            ],
        ];
    }
}
