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

use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;
use PrestaShop\Module\PrestashopCheckout\Builder\ModuleLink\ModuleLinkBuilder;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Faq\Faq;
use PrestaShop\Module\PrestashopCheckout\OnBoarding\Step\LiveStep;
use PrestaShop\Module\PrestashopCheckout\OnBoarding\Step\ValueBanner;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\Module\PrestashopCheckout\Repository\OrderRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Shop\ShopProvider;
use PrestaShop\Module\PrestashopCheckout\ShopContext;
use PrestaShop\Module\PrestashopCheckout\Translations\Translations;

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
    private $moduleKey;

    /**
     * @var PrestaShopContext
     */
    private $psContext;

    /**
     * @var PayPalConfiguration
     */
    private $paypalConfiguration;

    /**
     * @var LiveStep
     */
    private $liveStep;

    /**
     * @var ValueBanner
     */
    private $valueBanner;

    /**
     * @var Translations
     */
    private $translations;

    /**
     * @var ShopContext
     */
    private $shopContext;

    /**
     * @var ShopProvider
     */
    private $shopProvider;

    /**
     * @var ModuleLinkBuilder
     */
    private $moduleLinkBuilder;
    /**
     * @var PsAccountRepository
     */
    private $psAccountRepository;

    /**
     * @param string $moduleName
     * @param string $moduleKey
     * @param PrestaShopContext $psContext
     * @param PayPalConfiguration $payPalConfiguration
     * @param LiveStep $liveStep
     * @param ValueBanner $valueBanner
     * @param Translations $translations
     * @param ShopContext $shopContext
     * @param ShopProvider $shopProvider
     */
    public function __construct(
        $moduleName,
        $moduleKey,
        PrestaShopContext $psContext,
        PayPalConfiguration $payPalConfiguration,
        LiveStep $liveStep,
        ValueBanner $valueBanner,
        Translations $translations,
        ShopContext $shopContext,
        ShopProvider $shopProvider,
        ModuleLinkBuilder $moduleLinkBuilder,
        PsAccountRepository $psAccountRepository
    ) {
        $this->moduleName = $moduleName;
        $this->moduleKey = $moduleKey;
        $this->psContext = $psContext;
        $this->paypalConfiguration = $payPalConfiguration;
        $this->liveStep = $liveStep;
        $this->valueBanner = $valueBanner;
        $this->translations = $translations;
        $this->shopContext = $shopContext;
        $this->shopProvider = $shopProvider;
        $this->moduleLinkBuilder = $moduleLinkBuilder;
        $this->psAccountRepository = $psAccountRepository;
    }

    /**
     * Present the context module (vuex)
     *
     * @return array
     */
    public function present()
    {
        $shopId = (int) \Context::getContext()->shop->id;

        return [
            'context' => [
                'moduleVersion' => \Ps_checkout::VERSION,
                'moduleIsEnabled' => (bool) \Module::isEnabled('ps_checkout'),
                'psVersion' => _PS_VERSION_,
                'phpVersion' => phpversion(),
                'shopIs17' => $this->shopContext->isShop17(),
                'moduleKey' => $this->moduleKey,
                'shopId' => $this->psAccountRepository->getShopUuid(),
                'shopUri' => $this->shopProvider->getShopUrl($shopId),
                'isReady' => $this->shopContext->isReady(),
                'isShopContext' => $this->isShopContext(),
                'shopsTree' => $this->getShopsTree(),
                'faq' => $this->getFaq(),
                'language' => $this->psContext->getLanguage(),
                'prestashopCheckoutAjax' => $this->getGeneratedLink('AdminAjaxPrestashopCheckout'),
                'translations' => $this->translations->getTranslations(),
                'readmeUrl' => $this->getReadme(),
                'cguUrl' => $this->getCgu(),
                'privacyPolicyUrl' => $this->getPrivacyPolicyUrl(),
                'pricingUrl' => $this->getPricingUrl(),
                'roundingSettingsIsCorrect' => $this->paypalConfiguration->IsRoundingSettingsCorrect(),
                'liveStepConfirmed' => $this->liveStep->isConfirmed(),
                'liveStepViewed' => $this->liveStep->isViewed(),
                'valueBannerClosed' => $this->valueBanner->isClosed(),
                'youtubeInstallerLink' => $this->getYoutubeInstallerLink(),
                'incompatibleCountryCodes' => $this->paypalConfiguration->getIncompatibleCountryCodes(),
                'incompatibleCurrencyCodes' => $this->paypalConfiguration->getIncompatibleCurrencyCodes(),
                'countriesLink' => $this->getGeneratedLink('AdminCountries'),
                'currenciesLink' => $this->getGeneratedLink('AdminCurrencies'),
                'paymentPreferencesLink' => $this->getGeneratedLink($this->shopContext->isShop17() ? 'AdminPaymentPreferences' : 'AdminPayment'),
                'maintenanceLink' => $this->getGeneratedLink('AdminMaintenance'),
                'overridesExist' => $this->overridesExist(),
                'submitIdeaLink' => $this->getSubmitIdeaLink(),
                'orderTotal' => (new OrderRepository())->count($this->psContext->getShopId()),
                'isCustomTheme' => $this->shopUsesCustomTheme(),
                'callbackUrl' => $this->moduleLinkBuilder->getPaypalOnboardingCallBackUrl(),
                'dependencies' => [
                    'ps_eventbus' => \Module::isEnabled('ps_eventbus'),
                    'ps_accounts' => \Module::isEnabled('ps_accounts'),
                ],
            ],
        ];
    }

    /**
     * @return bool
     */
    private function isShopContext()
    {
        if (\Shop::isFeatureActive() && \Shop::getContext() !== \Shop::CONTEXT_SHOP) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    private function getShopsTree()
    {
        $shopList = [];

        if (true === $this->isShopContext()) {
            return $shopList;
        }

        $linkAdapter = new LinkAdapter($this->psContext->getLink());

        foreach (\Shop::getTree() as $groupId => $groupData) {
            $shops = [];

            foreach ($groupData['shops'] as $shopId => $shopData) {
                $shops[] = [
                    'id' => $shopId,
                    'name' => $shopData['name'],
                    'url' => $linkAdapter->getAdminLink(
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
     * Retrieve the faq
     *
     * @return array|bool faq or false if no faq associated to the module
     */
    private function getFaq()
    {
        $faq = new Faq();
        $faq->setModuleKey($this->moduleKey);
        $faq->setPsVersion(_PS_VERSION_);
        $faq->setIsoCode($this->psContext->getLanguageIsoCode());

        $response = $faq->getFaq();

        // If no response in the selected language, retrieve the faq in the default language (english)
        if (false === $response && $faq->getIsoCode() !== 'en') {
            $faq->setIsoCode('en');
            $response = $faq->getFaq();
        }

        return $response;
    }

    /**
     * Get the documentation url depending on the current language
     *
     * @return string path of the doc
     */
    private function getReadme()
    {
        $isoCode = $this->psContext->getLanguageIsoCode();

        $availableReadme = ['fr', 'en', 'it', 'es', 'nl', 'pl', 'pt'];

        if (!in_array($isoCode, $availableReadme)) {
            $isoCode = 'en';
        }

        return ''; // @todo To complete with new links
    }

    /**
     * Get the CGU url
     *
     * @return string path of the doc
     */
    private function getCgu()
    {
        $isoCode = $this->psContext->getLanguageIsoCode();

        switch ($isoCode) {
            case 'fr':
                return 'https://www.prestashop.com/fr/prestashop-checkout-conditions-generales-utilisation';
            case 'es':
                return 'https://www.prestashop.com/es/prestashop-checkout-condiciones-generales-uso';
            case 'it':
                return 'https://www.prestashop.com/it/prestashop-checkout-condizioni-generali-utilizzo';
            default:
                return 'https://www.prestashop.com/en/prestashop-checkout-general-terms-use';
        }
    }

    /**
     * Get the youtube link to help people to install PS_Checkout
     *
     * @return string
     */
    private function getYoutubeInstallerLink()
    {
        $isoCode = $this->psContext->getLanguageIsoCode();
        $youtube = 'https://www.youtube.com/embed/';
        switch ($isoCode) {
            case 'fr':
                return $youtube . 'TVShtzk5eUM';
            case 'es':
                return $youtube . 'CjfhyR368Q0';
            case 'it':
                return $youtube . 'bBHuojwH2V8';
            default:
                return $youtube . 'uovtJVCLaD8';
        }
    }

    /**
     * Get the countries link
     *
     * @return string
     */
    private function getCountriesLink()
    {
        $linkAdapter = new LinkAdapter($this->psContext->getLink());

        return $linkAdapter->getAdminLink('AdminCountries');
    }

    /**
     * Get a generated link
     *
     * @param string $link
     *
     * @return string
     */
    public function getGeneratedLink($link)
    {
        $linkAdapter = new LinkAdapter($this->psContext->getLink());

        return $linkAdapter->getAdminLink($link);
    }

    /**
     * Get bool value if there are overrides for ps_checkout
     *
     * @return bool
     */
    public function overridesExist()
    {
        $moduleOverridePath = _PS_OVERRIDE_DIR_ . 'modules/' . $this->moduleName;
        $themeModuleOverridePath = _PS_ALL_THEMES_DIR_ . $this->psContext->getCurrentThemeName() . '/modules/' . $this->moduleName;

        return is_dir($moduleOverridePath) || is_dir($themeModuleOverridePath);
    }

    /**
     * Get URL for
     *
     * @return string
     */
    private function getSubmitIdeaLink()
    {
        return 'https://portal.productboard.com/prestashop/1-prestashop-feedback-the-place-to-share-your-feedback-on-prestashop-s-next-features/tabs/9-prestashop-checkout';
    }

    /**
     * @return string
     */
    private function getPrivacyPolicyUrl()
    {
        $isoCode = $this->psContext->getLanguageIsoCode();

        switch ($isoCode) {
            case 'fr':
                return 'https://www.prestashop.com/fr/politique-protection-donnees-prestashop-download';
            default:
                return 'https://www.prestashop.com/en/personal-data-protection-policy-prestashop-download';
        }
    }

    /**
     * @return string
     */
    private function getPricingUrl()
    {
        $isoCode = $this->psContext->getLanguageIsoCode();

        switch ($isoCode) {
            case 'fr':
                return 'https://www.prestashop.com/fr/prestashop-checkout';
            case 'es':
                return 'https://www.prestashop.com/es/prestashop-checkout';
            case 'it':
                return 'https://www.prestashop.com/it/prestashop-checkout';
            case 'nl':
                return 'https://www.prestashop.com/nl/prestashop-checkout';
            case 'pt':
                return 'https://www.prestashop.com/pt/prestashop-checkout';
            case 'pl':
                return 'https://www.prestashop.com/pl/prestashop-checkout';
            default:
                return 'https://www.prestashop.com/en/prestashop-checkout';
        }
    }

    private function shopUsesCustomTheme()
    {
        return !in_array($this->psContext->getCurrentThemeName(), ['classic', 'default-bootstrap']);
    }
}
