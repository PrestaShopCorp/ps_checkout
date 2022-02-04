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
import { DefaultSelectors1_7 } from './default-selectors/default-selectors-ps1_7';

const SELECTORS = {
  ...DefaultSelectors1_7,
  ...(window.ps_checkout.selectors || {})
};

export class QuerySelectorPs1_7Service {
  static getBasePaymentConfirmation() {
    return document.querySelector(SELECTORS.BASE_PAYMENT_CONFIRMATION);
  }

  static getConditionsCheckboxes() {
    return Array.prototype.slice.call(
      document.querySelectorAll(SELECTORS.CONDITIONS_CHECKBOXES)
    );
  }

  static getLoaderParent() {
    return document.querySelector(SELECTORS.LOADER_PARENT);
  }

  static getNotificationConditions() {
    return document.querySelector(SELECTORS.NOTIFICATION_CONDITIONS);
  }

  static getNotificationPaymentCanceled() {
    return document.querySelector(SELECTORS.NOTIFICATION_PAYMENT_CANCELLED);
  }

  static getNotificationPaymentError() {
    return document.querySelector(SELECTORS.NOTIFICATION_PAYMENT_ERROR);
  }

  static getNotificationPaymentErrorText() {
    return document.querySelector(SELECTORS.NOTIFICATION_PAYMENT_ERROR_TEXT);
  }

  static getPaymentOptions() {
    return document.querySelector(SELECTORS.PAYMENT_OPTIONS);
  }

  static getPaymentOptionsLoader() {
    return document.querySelector(SELECTORS.PAYMENT_OPTIONS_LOADER);
  }

  static getPaymentOptionRadios() {
    return Array.prototype.slice.call(
      document.querySelectorAll(SELECTORS.PAYMENT_OPTION_RADIOS)
    );
  }

  static getCheckoutExpressCheckoutButtonContainerCart() {
    return document.querySelector(
      SELECTORS.EXPRESS_CHECKOUT_CONTAINER_CART_PAGE
    );
  }

  static getCheckoutExpressCheckoutButtonContainerCheckout() {
    return document.querySelector(
      SELECTORS.EXPRESS_CHECKOUT_CONTAINER_CHECKOUT_PAGE
    );
  }

  static getCheckoutExpressCheckoutButtonContainerProduct() {
    return document.querySelector(
      SELECTORS.EXPRESS_CHECKOUT_CONTAINER_PRODUCT_PAGE
    );
  }
}
