/**
 * 2007-2020 PrestaShop and Contributors
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
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
import * as types from './mutation-types';

export default {
  [types.UPDATE_PAYMENT_METHODS_ORDER](state, payload) {
    Object.assign(state.paymentMethods, payload);
  },
  [types.UPDATE_PAYMENT_MODE](state, payload) {
    state.paymentMode = payload;
  },
  [types.UPDATE_CAPTURE_MODE](state, payload) {
    state.captureMode = payload;
  },
  [types.UPDATE_PAYMENT_CARD_AVAILABILITY](state, payload) {
    state.cardIsEnabled = payload;
  },
  [types.UPDATE_EC_ORDER_PAGE](state, payload) {
    state.expressCheckout.orderPage = payload;
  },
  [types.UPDATE_EC_CHECKOUT_PAGE](state, payload) {
    state.expressCheckout.checkoutPage = payload;
  },
  [types.UPDATE_EC_PRODUCT_PAGE](state, payload) {
    state.expressCheckout.productPage = payload;
  },
  [types.UPDATE_LOGGER_LEVEL](state, payload) {
    state.logger.level = payload;
  },
  [types.UPDATE_LOGGER_MAX_FILES](state, payload) {
    state.logger.maxFiles = payload;
  },
  [types.UPDATE_LOGGER_HTTP](state, payload) {
    state.logger.http = payload;
  },
  [types.UPDATE_LOGGER_HTTP_FORMAT](state, payload) {
    state.logger.httpFormat = payload;
  },
};
