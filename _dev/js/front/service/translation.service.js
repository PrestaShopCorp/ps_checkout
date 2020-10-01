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
const TRANSLATION_MAP = {
  en: {
    'express-button.cart.separator': 'or',
    'express-button.checkout.express-checkout': 'Express Checkout',

    'paypal.hosted-fields.card-number': 'Card number',
    'paypal.hosted-fields.cvv': 'XXX',
    'paypal.hosted-fields.expiration-date': 'MM/YY',

    'error.paypal-skd': 'No PayPal Javascript SDK Instance'
  },

  fr: {
    'express-button.cart.separator': 'ou',
    'express-button.checkout.express-checkout': 'Achat Rapide'
  }
};

export class TranslationService {
  constructor(locale, defaultLocale = 'en', fallbackLocale = 'en') {
    this.locale = locale || defaultLocale;
    this.fallbackLocale = fallbackLocale;
  }

  getTranslationString(id, locale = this.locale) {
    return (
      TRANSLATION_MAP[locale][id] || TRANSLATION_MAP[this.fallbackLocale][id]
    );
  }
}
