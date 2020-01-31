<?php
/**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use PrestaShop\Module\PrestashopCheckout\Faq\Faq;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\Module\PrestashopCheckout\ShopContext;
use PrestaShop\Module\PrestashopCheckout\Translations\Translations;

/**
 * Construct the context module
 */
class ContextModule implements PresenterInterface
{
    /**
     * @var \Module
     */
    private $module;

    /**
     * @var \Context
     */
    private $context;

    public function __construct(\Module $module, \Context $context)
    {
        $this->module = $module;
        $this->context = $context;
    }

    /**
     * Present the context module (vuex)
     *
     * @return array
     */
    public function present()
    {
        $contextModule = [
            'context' => [
                'moduleVersion' => \Ps_checkout::VERSION,
                'psVersion' => _PS_VERSION_,
                'moduleKey' => $this->module->module_key,
                'shopId' => \Configuration::get('PS_CHECKOUT_SHOP_UUID_V4'),
                'isReady' => (new ShopContext())->isReady(),
                'faq' => $this->getFaq(),
                'language' => $this->context->language,
                'prestashopCheckoutAjax' => $this->context->link->getAdminLink('AdminAjaxPrestashopCheckout'),
                'translations' => (new Translations($this->module))->getTranslations(),
                'readmeUrl' => $this->getReadme(),
                'cguUrl' => $this->getCgu(),
                'roundingSettingsIsCorrect' => $this->roundingSettingsIsCorrect(),
                'showModuleRate' => $this->getStatusOfRateModule(),
            ],
        ];

        return $contextModule;
    }

    /**
     * Retrieve the faq
     *
     * @return array|bool faq or false if no faq associated to the module
     */
    private function getFaq()
    {
        $faq = new Faq();
        $faq->setModuleKey($this->module->module_key);
        $faq->setPsVersion(_PS_VERSION_);
        $faq->setIsoCode($this->context->language->iso_code);

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
        $isoCode = $this->context->language->iso_code;

        $availableReadme = ['fr', 'en', 'it', 'es'];

        if (!in_array($isoCode, $availableReadme)) {
            $isoCode = 'en';
        }

        return _MODULE_DIR_ . $this->module->name . '/docs/readme_' . $isoCode . '.pdf';
    }

    /**
     * Get the CGU url
     *
     * @return string path of the doc
     */
    private function getCgu()
    {
        $isoCode = $this->context->language->iso_code;

        switch ($isoCode) {
            case 'fr':
                return 'https://www.prestashop.com/fr/prestashop-checkout-conditions-generales-utilisation';
                break;
            case 'es':
                return 'https://www.prestashop.com/es/prestashop-checkout-condiciones-generales-uso';
                break;
            case 'it':
                return 'https://www.prestashop.com/it/prestashop-checkout-condizioni-generali-utilizzo';
                break;
            default:
                return 'https://www.prestashop.com/en/prestashop-checkout-general-terms-use';
                break;
        }
    }

    /**
     * Check if the rounding configuration if correctly set
     *
     * PS_ROUND_TYPE need to be set to 1 (Round on each item)
     * PS_PRICE_ROUND_MODE need to be set to 2 (Round up away from zero, when it is half way there)
     *
     * @return bool
     */
    private function roundingSettingsIsCorrect()
    {
        return \Configuration::get('PS_ROUND_TYPE') === '1'
            && \Configuration::get('PS_PRICE_ROUND_MODE') === '2';
    }

    /**
     * Tells if we show the Rate Module's section
     * We show the rate section only when the module has been installed for at least 8 days or less than 92 days
     *
     * @return bool
     */
    private function getStatusOfRateModule()
    {
        $now = new \DateTime('now');

        if (false === \Configuration::get('PS_CHECKOUT_DATE_INSTALL')) {
            $installedDate = $now->format('Y-m-d H:i:s');
            $dateHistory = $this->getModuleDateAddHistory();

            if (false !== $dateHistory) {
                $installedDate = $dateHistory;
            }

            \Configuration::updateValue('PS_CHECKOUT_DATE_INSTALL', $installedDate);

            return false;
        }

        $showRateModule = \DateTime::createFromFormat('Y-m-d H:i:s', \Configuration::get('PS_CHECKOUT_DATE_INSTALL'));
        $numberOfDays = (int) $now->diff($showRateModule)->format('%a');

        return $numberOfDays > 7 && $numberOfDays < 92;
    }

    /**
     * Get the module date history if exists
     *
     * @return string|false
     */
    private function getModuleDateAddHistory()
    {
        return \Db::getInstance()->getValue(
            'SELECT date_add
            FROM `' . _DB_PREFIX_ . 'module_history`
            WHERE id_module = ' . (int) $this->module->id
        );
    }
}
