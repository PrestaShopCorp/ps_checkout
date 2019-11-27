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

use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;

/**
 * Construct the Psx module
 */
class PsxModule implements PresenterInterface
{
    const ALL_LANGUAGES_FILE = _PS_ROOT_DIR_ . '/app/Resources/all_languages.json';
    const ALL_COUNTRIES_FILE = _PS_MODULE_DIR_ . 'ps_checkout/views/json/all_countries.json';
    const ALL_COUNTRIES_STATES_FILE = _PS_MODULE_DIR_ . 'ps_checkout/views/json/countries_states.json';
    const ALL_BUSINESS_FILE = _PS_MODULE_DIR_ . 'ps_checkout/views/json/i18n/business-information-';

    /**
     * @var \Context
     */
    private $context;

    public function __construct(\Context $context)
    {
        $this->context = $context;
    }

    /**
     * Present the Psx module (vuex)
     *
     * @return array
     */
    public function present()
    {
        return [
            'psx' => [
                'onboardingCompleted' => (new PsAccountRepository())->psxFormIsCompleted(),
                'psxFormData' => json_decode(\Configuration::get('PS_CHECKOUT_PSX_FORM'), true),
                'languagesDetails' => $this->getJsonData(self::ALL_LANGUAGES_FILE),
                'countriesDetails' => $this->getJsonData(self::ALL_COUNTRIES_FILE),
                'countriesStatesDetails' => $this->getJsonData(self::ALL_COUNTRIES_STATES_FILE),
                'businessDetails' => $this->getJsonData($this->getBusinessFileName()),
            ],
        ];
    }

    /**
     * getJsonData
     *
     * @param string $dir
     *
     * @return array $data
     */
    private function getJsonData($dir)
    {
        $data = json_decode(
            file_get_contents($dir),
            true
        );

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception(sprintf('The legacy to standard locales JSON could not be decoded %s', json_last_error_msg()));
        }

        return $data;
    }

    /**
     * getBusinessFileName
     */
    private function getBusinessFileName()
    {
        $employeeLanguageIsoCode = $this->context->language->iso_code;

        if (file_exists(self::ALL_BUSINESS_FILE . $employeeLanguageIsoCode . '.json')) {
            return self::ALL_BUSINESS_FILE . $employeeLanguageIsoCode . '.json';
        }

        return self::ALL_BUSINESS_FILE . 'en.json';
    }
}
