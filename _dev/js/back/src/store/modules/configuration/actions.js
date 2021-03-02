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

  togglePayIn4XOrderPage({ commit, getters }, payload) {
    return ajax({
      url: getters.adminController,
      action: 'TogglePayIn4XOrderPage',
      data: {
        status: payload ? 1 : 0
      }
    }).then(() => {
      commit(types.UPDATE_PAY_IN_4X_ORDER_PAGE, payload);
      return Promise.resolve(payload);
    });
  },

  togglePayIn4XProductPage({ commit, getters }, payload) {
    return ajax({
      url: getters.adminController,
      action: 'TogglePayIn4XProductPage',
      data: {
        status: payload ? 1 : 0
      }
    }).then(() => {
      commit(types.UPDATE_PAY_IN_4X_PRODUCT_PAGE, payload);
      return Promise.resolve(payload);
    });
  },

  toggleECOrderPage({ commit, getters }, payload) {
    return ajax({
      url: getters.adminController,
      action: 'ToggleECOrderPage',
      data: {
        status: payload ? 1 : 0
      }
    }).then(() => {
      commit(types.UPDATE_EC_ORDER_PAGE, payload);
      return Promise.resolve(payload);
    });
  },

  toggleECCheckoutPage({ commit, getters }, payload) {
    return ajax({
      url: getters.adminController,
      action: 'ToggleECCheckoutPage',
      data: {
        status: payload ? 1 : 0
      }
    }).then(() => {
      commit(types.UPDATE_EC_CHECKOUT_PAGE, payload);
      return Promise.resolve(payload);
    });
  },

  toggleECProductPage({ commit, getters }, payload) {
    return ajax({
      url: getters.adminController,
      action: 'ToggleECProductPage',
      data: {
        status: payload ? 1 : 0
      }
    }).then(() => {
      commit(types.UPDATE_EC_PRODUCT_PAGE, payload);
      return Promise.resolve(payload);
    });
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
