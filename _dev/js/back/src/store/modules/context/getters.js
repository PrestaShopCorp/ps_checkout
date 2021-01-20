/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
import { isOnboardingCompleted } from 'prestashop_accounts_vue_components';
export default {
  adminController: state => state.prestashopCheckoutAjax,
  locale: state => state.language.iso_code,
  shopIs17: state => state.shopIs17,
  translations: state => state.translations,
  roundingSettingsIsCorrect: state => state.roundingSettingsIsCorrect,
  shopId: state => state.shopId,
  incompatibleCountryCodes: state => state.incompatibleCountryCodes,
  incompatibleCurrencyCodes: state => state.incompatibleCurrencyCodes,
  countriesLink: state => state.countriesLink,
  currenciesLink: state => state.currenciesLink,
  paymentPreferencesLink: state => state.paymentPreferencesLink,
  merchantIsFullyOnboarded: (state, getters) =>
    getters.paypalOnboardingIsCompleted &&
    (isOnboardingCompleted() || getters.firebaseOnboardingIsCompleted) &&
    getters.psxOnboardingIsCompleted
};
