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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\OnBoarding\Helper\OnboardingStatusHelper;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\PsAccountsInstaller\Installer\Installer;

class OnboardingModule implements PresenterInterface
{
    /**
     * @var OnboardingStatusHelper
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
    /**
     * @var PrestaShopConfiguration
     */
    private $configuration;

    public function __construct(
        OnboardingStatusHelper $onBoardingStatusHelper,
        PrestaShopContext $prestaShopContext,
        Installer $installer,
        PrestaShopConfiguration $configuration
    ) {
        $this->onBoardingStatusHelper = $onBoardingStatusHelper;
        $this->prestaShopContext = $prestaShopContext;
        $this->installer = $installer;
        $this->configuration = $configuration;
    }

    public function present()
    {
        return [
            'onboarding' => [
                'psAccountsOnboarded' => $this->onBoardingStatusHelper->isPsAccountsOnboarded(),
                'psCheckoutOnboarded' => $this->onBoardingStatusHelper->isPsCheckoutOnboarded(),
                'psAccountsEnabled' => $this->installer->isModuleEnabled(),
                'loginWithPsCheckoutAvailable' => $this->onBoardingStatusHelper->isPsCheckoutLoginAllowed(),
                'psAccountsConfigureURL' => $this->prestaShopContext->getLink()->getAdminLink(
                    'AdminModules',
                    true,
                    [],
                    [
                        'configure' => 'ps_accounts',
                    ]
                ),
            ],
        ];
    }
}
