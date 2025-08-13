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

namespace PsCheckout\Presentation\Presenter\Settings\Admin\Modules;

use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Presentation\Presenter\PresenterInterface;

/**
 * Construct the context module
 */
class ContextModule implements PresenterInterface
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var string
     */
    private $moduleVersion;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var LinkInterface
     */
    private $link;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @param string $moduleName
     * @param string $moduleVersion
     * @param ContextInterface $context
     * @param LinkInterface $link
     * @param ConfigurationInterface $configuration
     */
    public function __construct(
        string $moduleName,
        string $moduleVersion,
        ContextInterface $context,
        LinkInterface $link,
        ConfigurationInterface $configuration
    ) {
        $this->moduleName = $moduleName;
        $this->moduleVersion = $moduleVersion;
        $this->context = $context;
        $this->link = $link;
        $this->configuration = $configuration;
    }

    /**
     * Present the context module (vuex)
     *
     * @return array
     */
    public function present(): array
    {
        $shopId = (int) \Context::getContext()->shop->id;

        return [
            'context' => [
                'moduleVersion' => $this->moduleVersion,
                'moduleIsEnabled' => (bool) \Module::isEnabled('ps_checkout'),
                'psVersion' => _PS_VERSION_,
                'phpVersion' => phpversion(),
                'shopUri' => $this->getShopUri($shopId),
                'isShopContext' => $this->isShopContext(),
                'shopsTree' => $this->getShopsTree(),
                'language' => $this->context->getLanguage(),
                'roundingSettingsIsCorrect' => $this->isRoundingSettingsCorrect(),
                'overridesExist' => $this->overridesExist(),
                'prestashopCheckoutAjax' => $this->link->getAdminLink('AdminAjaxPrestashopCheckout'),
                'paymentPreferencesLink' => $this->link->getAdminLink('AdminPaymentPreferences'),
                'maintenanceLink' => $this->link->getAdminLink('AdminMaintenance'),
                'callbackUrl' => $this->link->getAdminLink(
                    'AdminModules',
                    true,
                    [],
                    [
                        'configure' => $this->moduleName,
                    ]
                ),
            ],
        ];
    }

    /**
     * @param int $shopId
     *
     * @return bool|string
     */
    private function getShopUri(int $shopId)
    {
        return (new \Shop($shopId))->getBaseURL();
    }

    /**
     * @return bool
     */
    private function isShopContext(): bool
    {
        if (\Shop::isFeatureActive() && \Shop::getContext() !== \Shop::CONTEXT_SHOP) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    private function getShopsTree(): array
    {
        $shopList = [];

        if (true === $this->isShopContext()) {
            return $shopList;
        }

        foreach (\Shop::getTree() as $groupId => $groupData) {
            $shops = [];

            foreach ($groupData['shops'] as $shopId => $shopData) {
                $shops[] = [
                    'id' => $shopId,
                    'name' => $shopData['name'],
                    'url' => $this->link->getAdminLink(
                        'AdminModules',
                        true,
                        [],
                        [
                            'configure' => $this->moduleName,
                            'setShopContext' => 's-' . $shopId,
                        ]
                    ),
                ];
            }

            $shopList[] = [
                'id' => $groupId,
                'name' => $groupData['name'],
                'shops' => $shops,
            ];
        }

        return $shopList;
    }

    /**
     * Get bool value if there are overrides for ps_checkout
     *
     * @return bool
     */
    private function overridesExist(): bool
    {
        $moduleOverridePath = _PS_OVERRIDE_DIR_ . 'modules/' . $this->moduleName;
        $themeModuleOverridePath = _PS_ALL_THEMES_DIR_ . $this->context->getCurrentThemeName() . '/modules/' . $this->moduleName;

        return is_dir($moduleOverridePath) || is_dir($themeModuleOverridePath);
    }

    private function isRoundingSettingsCorrect(): bool
    {
        return $this->configuration->get(PayPalConfiguration::PS_ROUND_TYPE) === PayPalConfiguration::ROUND_ON_EACH_ITEM
            && $this->configuration->get(PayPalConfiguration::PS_PRICE_ROUND_MODE) === PayPalConfiguration::ROUND_UP_AWAY_FROM_ZERO;
    }
}
