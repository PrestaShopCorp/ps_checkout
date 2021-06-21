<?php

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use PrestaShop\Module\PrestashopCheckout\OnBoarding\Helper\OnBoardingStatusHelper;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;

class OnboardingModule implements PresenterInterface
{
    /**
     * @var OnBoardingStatusHelper
     */
    private $onBoardingStatusHelper;

    public function __construct(OnBoardingStatusHelper $onBoardingStatusHelper)
    {
        $this->onBoardingStatusHelper = $onBoardingStatusHelper;
    }

    public function present()
    {
        return [
            'onboarding' => [
                'psAccountsOnboarded' => $this->onBoardingStatusHelper->isPsAccountsOnboarded(),
                'psCheckoutOnboarded' => $this->onBoardingStatusHelper->isPsCheckoutOnboarded(),
            ]
        ];
    }
}
