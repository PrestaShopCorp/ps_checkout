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

use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;

/**
 * Construct the Psx module
 */
class PsxModule implements PresenterInterface
{
    const ALL_LANGUAGES_FILE = _PS_MODULE_DIR_ . 'ps_checkout/views/json/all_languages.json';
    const ALL_COUNTRIES_FILE = _PS_MODULE_DIR_ . 'ps_checkout/views/json/all_countries.json';
    const ALL_COUNTRIES_STATES_FILE = _PS_MODULE_DIR_ . 'ps_checkout/views/json/countries_states.json';
    const ALL_BUSINESS_FILE = _PS_MODULE_DIR_ . 'ps_checkout/views/json/i18n/business-information-';

    /**
     * @var PrestaShopContext
     */
    private $context;

    /**
     * @var PsAccountRepository
     */
    private $psAccount;

    public function __construct(PrestaShopContext $context, PsAccountRepository $psAccount)
    {
        $this->context = $context;
        $this->psAccount = $psAccount;
    }

    /**
     * Present the Psx module (vuex)
     *
     * @return array
     *
     * @throws PsCheckoutException
     */
    public function present()
    {
        return [
            'psx' => [
                'onboardingCompleted' => $this->psAccount->psxFormIsCompleted(),
                'psxFormData' => $this->psAccount->getPsxForm(true),
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
     *
     * @throws PsCheckoutException
     */
    private function getJsonData($dir)
    {
        $data = json_decode(
            file_get_contents($dir),
            true
        );

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new PsCheckoutException(sprintf('The legacy to standard locales JSON could not be decoded %s', json_last_error_msg()), PsCheckoutException::PSCHECKOUT_LOCALE_DECODE_ERROR);
        }

        return $data;
    }

    /**
     * getBusinessFileName
     */
    private function getBusinessFileName()
    {
        $employeeLanguageIsoCode = $this->context->getLanguageIsoCode();

        if (file_exists(self::ALL_BUSINESS_FILE . $employeeLanguageIsoCode . '.json')) {
            return self::ALL_BUSINESS_FILE . $employeeLanguageIsoCode . '.json';
        }

        return self::ALL_BUSINESS_FILE . 'en.json';
    }
}
