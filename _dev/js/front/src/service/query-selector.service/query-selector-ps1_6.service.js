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
import { DefaultSelectors1_6 } from './default-selectors/default-selectors-ps1_6';

const SELECTORS = {
  ...DefaultSelectors1_6,
  ...(window.ps_checkout.selectors || {})
};

export class QuerySelectorPs1_6Service {
  static getBasePaymentConfirmation() {
    return this.querySelector(SELECTORS.BASE_PAYMENT_CONFIRMATION);
  }

  static getConditionsCheckboxes() {
    return this.querySelectorAll(SELECTORS.CONDITIONS_CHECKBOXES);
  }

  static getLoaderParent() {
    return this.querySelector(SELECTORS.LOADER_PARENT);
  }

  static getNotificationConditions() {
    return this.querySelector(SELECTORS.NOTIFICATION_CONDITIONS);
  }

  static getNotificationPaymentCanceled() {
    return this.querySelector(SELECTORS.NOTIFICATION_PAYMENT_CANCELLED);
  }

  static getNotificationPaymentError() {
    return this.querySelector(SELECTORS.NOTIFICATION_PAYMENT_ERROR);
  }

  static getNotificationPaymentErrorText() {
    return this.querySelector(SELECTORS.NOTIFICATION_PAYMENT_ERROR_TEXT);
  }

  static getPaymentOptions() {
    return this.querySelector(SELECTORS.PAYMENT_OPTIONS);
  }

  static getPaymentOptionsLoader() {
    return this.querySelector(SELECTORS.PAYMENT_OPTIONS_LOADER);
  }

  static getPaymentOptionRadios() {
    return this.querySelectorAll(SELECTORS.PAYMENT_OPTION_RADIOS);
  }

  static getExpressCheckoutButtonContainerCart() {
    return this.querySelector(
      SELECTORS.EXPRESS_CHECKOUT_CONTAINER_CART_PAGE
    );
  }

  static getExpressCheckoutButtonContainerCheckout() {
    return this.querySelector(
      SELECTORS.EXPRESS_CHECKOUT_CONTAINER_CHECKOUT_PAGE
    );
  }

  static getExpressCheckoutButtonContainerProduct() {
    return this.querySelector(
      SELECTORS.EXPRESS_CHECKOUT_CONTAINER_PRODUCT_PAGE
    );
  }

  static getCardFieldsFormContainer() {
    return this.querySelector(
      SELECTORS.CARD_FIELDS.FORM
    );
  }

  static getCardFieldsNameInputContainer() {
    return this.querySelector(
      SELECTORS.CARD_FIELDS.NAME
    );
  }

  static getCardFieldsNameError() {
    return this.querySelector(
      SELECTORS.CARD_FIELDS.NAME_ERROR
    );
  }

  static getCardFieldsNumberInputContainer() {
    return this.querySelector(
      SELECTORS.CARD_FIELDS.NUMBER
    );
  }

  static getCardFieldsNumberError() {
    return this.querySelector(
      SELECTORS.CARD_FIELDS.NUMBER_ERROR
    );
  }

  static getCardFieldsVendorError() {
    return this.querySelector(
      SELECTORS.CARD_FIELDS.VENDOR_ERROR
    );
  }

  static getCardFieldsExpiryInputContainer() {
    return this.querySelector(
      SELECTORS.CARD_FIELDS.EXPIRY
    );
  }

  static getCardFieldsExpiryError() {
    return this.querySelector(
      SELECTORS.CARD_FIELDS.EXPIRY_ERROR
    );
  }

  static getCardFieldsCvvInputContainer() {
    return this.querySelector(
      SELECTORS.CARD_FIELDS.CVV
    );
  }

  static getCardFieldsCvvError() {
    return this.querySelector(
      SELECTORS.CARD_FIELDS.CVV_ERROR
    );
  }

  static getPayLaterOfferMessageContainerSelector(placement) {
    switch (placement) {
      case 'product':
        return this.querySelector(SELECTORS.PAY_LATER_OFFER_MESSAGE_CONTAINER_PRODUCT);
      case 'cart':
      case 'payment':
        return this.querySelector(SELECTORS.PAY_LATER_OFFER_MESSAGE_CONTAINER_CART_SUMMARY);
      default:
        return;
    }
  }

  static getPayLaterOfferBannerContainerSelector(placement) {
    switch (placement) {
      case 'product':
      case 'cart':
      case 'home':
      case 'payment':
      case 'category':
        return this.querySelector(SELECTORS.PAY_LATER_BANNER_CONTAINER);
      default:
        return;
    }
  }

  static querySelector(selector) {
    let element = document.querySelector(selector);

    if (!element) {
      console.error('HTMLElement selector ' + selector + ' not found.');
    }

    return element;
  }

  static querySelectorAll(selector) {
    let elements = Array.prototype.slice.call(
      document.querySelectorAll(selector)
    );

    if (!elements || elements.length === 0) {
      console.error('HTMLElement selector ' + selector + ' not found.');
    }

    return elements;
  }

  static getPaymentMethodLogoContainer(placement) {
    switch (placement) {
      case 'product':
        return document.querySelector(SELECTORS.PAYMENT_METHOD_LOGO_PRODUCT_CONTAINER);
      case 'cart':
        return document.querySelector(SELECTORS.PAYMENT_METHOD_LOGO_CART_CONTAINER);
      default:
        return;
    }
  }
}
