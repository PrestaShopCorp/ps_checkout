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
import { HtmlSelectorsPs1_7Constants } from '../../constants/html-selectors-ps1_7.constants';

export class HtmlElementPs1_7Service {
  constructor() {
    this.selectors = HtmlSelectorsPs1_7Constants;
  }

  getBasePaymentOption(cache = false) {
    if (!this.basePaymentOption || cache) {
      this.basePaymentOption = document.querySelector(
        this.selectors.ANY_PAYMENT_OPTION
      );
    }

    return this.basePaymentOption;
  }

  getButtonContainer(cache = false) {
    if (!this.buttonContainer || cache) {
      this.buttonContainer = document.getElementById(
        this.selectors.BUTTONS_CONTAINER_ID
      );
    }

    return this.buttonContainer;
  }

  getCheckoutExpressCartButtonContainer() {
    return document.getElementById(
      this.selectors.CHECKOUT_EXPRESS_CART_BUTTON_CONTAINER_ID
    );
  }

  getCheckoutExpressCheckoutButtonContainer() {
    return document.querySelector(
      this.selectors.CHECKOUT_EXPRESS_CHECKOUT_BUTTON_CONTAINER
    );
  }

  getCheckoutExpressProductButtonContainer() {
    return document.querySelector(
      this.selectors.CHECKOUT_EXPRESS_PRODUCT_BUTTON_CONTAINER
    );
  }

  getConditionsCheckboxContainer(cache = false) {
    if (!this.conditionsCheckboxContainer || cache) {
      this.conditionsCheckboxContainer = document.getElementById(
        this.selectors.CONDITIONS_CHECKBOX_CONTAINER_ID
      );
    }

    return this.conditionsCheckboxContainer;
  }

  getConditionsCheckboxes(container) {
    return container
      ? Array.prototype.slice.call(
          container.querySelectorAll(this.selectors.CONDITION_CHECKBOX)
        )
      : null;
  }

  getHostedFieldsForm(cache = false) {
    if (!this.hostedFieldsForm || cache) {
      this.hostedFieldsForm = document.getElementById(
        this.selectors.HOSTED_FIELDS_FORM_ID
      );
    }

    return this.hostedFieldsForm;
  }

  getNotificationConditions(cache = false) {
    if (!this.notificationConditions || cache) {
      this.notificationConditions = document.querySelector(
        this.selectors.NOTIFICATION_CONDITIONS
      );
    }

    return this.notificationConditions;
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

  getPaymentOption(container) {
    return container.querySelector(this.selectors.PAYMENT_OPTION);
  }

  getPaymentOptionLabel(container, text) {
    const items = Array.prototype.slice.call(container.querySelectorAll('*'));
    return items.find((item) => item.innerText === text);
  }

  getPaymentOptionLabelLegacy(container, id) {
    return container.querySelector(this.selectors.PAYMENT_OPTION_LABEL(id));
  }

  getPaymentOptionSelect(container) {
    return container.querySelector(this.selectors.PAYMENT_OPTION_SELECT);
  }

  getPaymentOptionContainer(id) {
    return document.getElementById(
      this.selectors.PAYMENT_OPTION_CONTAINER_ID(id)
    );
  }

  getPaymentOptionAdditionalInformation(id) {
    return document.getElementById(
      this.selectors.PAYMENT_OPTION_ADDITIONAL_INFORMATION_ID(id)
    );
  }

  getPaymentOptionFormContainer(id) {
    return document.getElementById(
      this.selectors.PAYMENT_OPTION_FORM_CONTAINER_ID(id)
    );
  }

  getPaymentOptionFormButton(container, id) {
    return container.querySelector(
      this.selectors.PAYMENT_OPTION_FORM_BUTTON(id)
    );
  }

  getPaymentOptionsContainer(cache = false) {
    if (!this.paymentOptionsContainer || cache) {
      this.paymentOptionsContainer = document.querySelector(
        this.selectors.PAYMENT_OPTIONS_CONTAINER
      );
    }

    return this.paymentOptionsContainer;
  }

  getPaymentOptions(cache = false) {
    if (!this.paymentOptions || cache) {
      this.paymentOptions = this.getPaymentOptionsContainer(
        cache
      ).querySelectorAll(this.selectors.PAYMENT_OPTION);

      this.paymentOptions = Array.prototype.slice.call(this.paymentOptions);
    }

    return this.paymentOptions;
  }
}
