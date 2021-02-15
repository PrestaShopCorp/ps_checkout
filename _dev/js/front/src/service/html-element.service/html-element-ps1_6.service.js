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
/* istanbul ignore file */
// TODO: Remove this service (replace this with QuerySelectorService or local methods)
import { HtmlSelectorsPs1_6Constants } from '../../constants/html-selectors-ps1_6.constants';

export class HtmlElementPs1_6Service {
  constructor() {
    this.selectors = HtmlSelectorsPs1_6Constants;
  }

  getBasePaymentOption() {
    return document.querySelector(this.selectors.ANY_PAYMENT_OPTION);
  }

  getCheckoutPaymentOptionsContainer() {
    return document.querySelector(
      this.selectors.CHECKOUT_PAYMENT_OPTIONS_CONTAINER
    );
  }

  getNotificationPaymentContainer(cache = false) {
    if (!this.notificationPaymentContainer || cache) {
      this.notificationPaymentContainer = document.getElementById(
        this.selectors.NOTIFICATION_CONTAINER_ID
      );
    }

    return this.notificationPaymentContainer;
  }

  getNotificationPaymentContainerTarget(cache = false) {
    if (!this.notificationPaymentContainerTarget || cache) {
      this.notificationPaymentContainerTarget = document.getElementById(
        this.selectors.NOTIFICATION_TARGET_ID
      );
    }

    return this.notificationPaymentContainerTarget;
  }

  getNotificationPaymentCanceled(cache = false) {
    if (!this.notificationPaymentCanceled || cache) {
      this.notificationPaymentCanceled = document.getElementById(
        this.selectors.NOTIFICATION_PAYMENT_CANCELED_ID
      );
    }

    return this.notificationPaymentCanceled;
  }

  getNotificationPaymentError(cache = false) {
    if (!this.notificationPaymentError || cache) {
      this.notificationPaymentError = document.getElementById(
        this.selectors.NOTIFICATION_PAYMENT_ERROR_ID
      );
    }

    return this.notificationPaymentError;
  }

  getNotificationPaymentErrorText(cache = false) {
    if (!this.notificationPaymentErrorText || cache) {
      this.notificationPaymentErrorText = document.getElementById(
        this.selectors.NOTIFICATION_PAYMENT_ERROR_TEXT_ID
      );
    }

    return this.notificationPaymentErrorText;
  }

  getPaymentOptionsContainer() {
    return document.getElementById(this.selectors.PAYMENT_OPTIONS_CONTAINER);
  }

  getPaymentOptions() {
    return Array.prototype.slice.call(
      this.getPaymentOptionsContainer().querySelectorAll(
        this.selectors.PAYMENT_OPTION
      )
    );
  }

  getPaymentOptionContainer(container) {
    return container.querySelector(this.selectors.PAYMENT_OPTION_CONTAINER);
  }
}
