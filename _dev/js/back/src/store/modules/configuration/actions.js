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
import * as types from './mutation-types';
import ajax from '@/requests/ajax.js';
import { toggleConfiguration } from '@/requests/toggle-configuration';

export default {
  updatePaymentMethods({ commit, getters }, payload) {
    return ajax({
      url: getters.adminController,
      action: 'UpdatePaymentMethodsOrder',
      data: {
        paymentMethods: JSON.stringify(payload.paymentMethods)
      }
    }).then(() => {
      commit(types.UPDATE_PAYMENT_METHODS_ORDER, payload.paymentMethods);
      return Promise.resolve(true);
    });
  },

  updatePaymentMode({ commit, getters }, payload) {
    return ajax({
      url: getters.adminController,
      action: 'UpdatePaymentMode',
      data: {
        paymentMode: payload
      }
    }).then(() => {
      commit(types.UPDATE_PAYMENT_MODE, payload);
      return Promise.resolve(true);
    });
  },

  updateCaptureMode({ commit, getters }, payload) {
    return ajax({
      url: getters.adminController,
      action: 'UpdateCaptureMode',
      data: {
        captureMode: payload
      }
    }).then(() => {
      commit(types.UPDATE_CAPTURE_MODE, payload);
      return Promise.resolve(true);
    });
  },

  updateCreditCardFields({ commit, getters }, payload) {
    return ajax({
      url: getters.adminController,
      action: 'UpdateCreditCardFields',
      data: {
        hostedFieldsEnabled: payload
      }
    }).then(() => {
      commit(types.UPDATE_CREDIT_CARD_FIELDS, payload);
      return Promise.resolve(true);
    });
  },

  togglePaymentOptionAvailability({ commit, getters }, payload) {
    return ajax({
      url: getters.adminController,
      action: 'TogglePaymentOptionAvailability',
      data: {
        paymentOption: JSON.stringify(payload.paymentOption)
      }
    }).then(() => {
      if ('card' === payload.paymentOption.name) {
        commit(
          types.UPDATE_PAYMENT_CARD_AVAILABILITY,
          payload.paymentOption.isEnabled
        );
      }
      return Promise.resolve(payload);
    });
  },

  togglePayLaterOrderPageMessage(store, payload) {
    return toggleConfiguration(
      store,
      payload,
      'TogglePayLaterOrderPageMessage',
      types.UPDATE_PAY_LATER_ORDER_PAGE_MESSAGE
    );
  },

  togglePayLaterProductPageMessage(store, payload) {
    return toggleConfiguration(
      store,
      payload,
      'TogglePayLaterProductPageMessage',
      types.UPDATE_PAY_LATER_PRODUCT_PAGE_MESSAGE
    );
  },

  togglePayLaterOrderPageBanner(store, payload) {
    return toggleConfiguration(
      store,
      payload,
      'TogglePayLaterOrderPageBanner',
      types.UPDATE_PAY_LATER_ORDER_PAGE_BANNER
    );
  },

  togglePayLaterProductPageBanner(store, payload) {
    return toggleConfiguration(
      store,
      payload,
      'TogglePayLaterProductPageBanner',
      types.UPDATE_PAY_LATER_PRODUCT_PAGE_BANNER
    );
  },

  togglePayLaterHomePageBanner(store, payload) {
    return toggleConfiguration(
      store,
      payload,
      'TogglePayLaterHomePageBanner',
      types.UPDATE_PAY_LATER_HOME_PAGE_BANNER
    );
  },

  togglePayLaterCategoryPageBanner(store, payload) {
    return toggleConfiguration(
      store,
      payload,
      'TogglePayLaterCategoryPageBanner',
      types.UPDATE_PAY_LATER_CATEGORY_PAGE_BANNER
    );
  },

  togglePayLaterCartPageButton(store, payload) {
    return toggleConfiguration(
      store,
      payload,
      'TogglePayLaterCartPageButton',
      types.UPDATE_PAY_LATER_CART_PAGE_BUTTON
    );
  },

  togglePayLaterOrderPageButton(store, payload) {
    return toggleConfiguration(
      store,
      payload,
      'TogglePayLaterOrderPageButton',
      types.UPDATE_PAY_LATER_ORDER_PAGE_BUTTON
    );
  },

  togglePayLaterProductPageButton(store, payload) {
    return toggleConfiguration(
      store,
      payload,
      'TogglePayLaterProductPageButton',
      types.UPDATE_PAY_LATER_PRODUCT_PAGE_BUTTON
    );
  },

  toggleECOrderPage(store, payload) {
    return toggleConfiguration(
      store,
      payload,
      'ToggleECOrderPage',
      types.UPDATE_EC_ORDER_PAGE
    );
  },

  toggleECCheckoutPage(store, payload) {
    return toggleConfiguration(
      store,
      payload,
      'ToggleECCheckoutPage',
      types.UPDATE_EC_CHECKOUT_PAGE
    );
  },

  toggleECProductPage(store, payload) {
    return toggleConfiguration(
      store,
      payload,
      'ToggleECProductPage',
      types.UPDATE_EC_PRODUCT_PAGE
    );
  },

  changeLoggerLevel({ commit, getters }, value) {
    return ajax({
      url: getters.adminController,
      action: 'UpdateLoggerLevel',
      data: {
        level: value
      }
    }).then(() => {
      commit(types.UPDATE_LOGGER_LEVEL, value);
      return value;
    });
  },

  changeLoggerMaxFiles({ commit, getters }, value) {
    return ajax({
      url: getters.adminController,
      action: 'UpdateLoggerMaxFiles',
      data: {
        maxFiles: value
      }
    }).then(() => {
      commit(types.UPDATE_LOGGER_MAX_FILES, value);
      return value;
    });
  },

  changeLoggerHttp({ commit, getters }, value) {
    return ajax({
      url: getters.adminController,
      action: 'UpdateLoggerHttp',
      data: {
        isEnabled: value
      }
    }).then(() => {
      commit(types.UPDATE_LOGGER_HTTP, value);
      return value;
    });
  },

  changeLoggerHttpFormat({ commit, getters }, value) {
    return ajax({
      url: getters.adminController,
      action: 'UpdateLoggerHttpFormat',
      data: {
        httpFormat: value
      }
    }).then(() => {
      commit(types.UPDATE_LOGGER_HTTP_FORMAT, value);
      return value;
    });
  },

  savePaypalButtonConfiguration({ commit, getters }, payload) {
    return ajax({
      url: getters.adminController,
      action: 'SavePaypalButtonConfiguration',
      data: {
        configuration: JSON.stringify(payload.configuration)
      }
    }).then(() => {
      commit(types.SAVE_PAYPAL_BUTTON_CONFIGURATION, payload);
      return Promise.resolve(true);
    });
  }
};
