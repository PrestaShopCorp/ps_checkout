<?php

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\OnBoarding\Helper\OnBoardingStatusHelper;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;

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

    public function __construct(OnBoardingStatusHelper $onBoardingStatusHelper, PrestaShopContext $prestaShopContext)
    {
        $this->onBoardingStatusHelper = $onBoardingStatusHelper;
        $this->prestaShopContext = $prestaShopContext;
    }

    public function present()
    {
        return [
            'onboarding' => [
                'psAccountsOnboarded' => $this->onBoardingStatusHelper->isPsAccountsOnboarded(),
                'psCheckoutOnboarded' => $this->onBoardingStatusHelper->isPsCheckoutOnboarded(),
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
            ]
        ];
    }
}
