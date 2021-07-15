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
  unlink({ commit, getters }) {
    return ajax({
      url: getters.adminController,
      action: 'LogOutPaypalAccount'
    }).then(() => {
      commit(types.UNLINK_ACCOUNT);
      return true;
    });
  },
  onboard({ getters }) {
    return ajax({
      url: getters.adminController,
      action: 'Onboard'
    }).then(response => {
      if (response.status === false) {
        throw response;
      }

      return Promise.resolve(response);
    });
  },
  updatePaypalStatusSettings({ commit, getters }) {
    return ajax({
      url: getters.adminController,
      action: 'LiveStepConfirmed'
    }).then(resp => {
      if (resp) {
        commit(types.UPDATE_CONFIRMED_LIVE_STEP, true);
        return true;
      }

      throw resp;
    });
  },
  updatePaypalStatusViewed({ commit, getters }) {
    return ajax({
      url: getters.adminController,
      action: 'LiveStepViewed'
    }).then(resp => {
      if (resp) {
        commit(types.UPDATE_VIEWED_LIVE_STEP, true);
        return true;
      }

      throw resp;
    });
  },
  updatePaypalValueBanner({ commit, getters }) {
    return ajax({
      url: getters.adminController,
      action: 'ValueBannerClosed'
    }).then(resp => {
      if (resp) {
        commit(types.UPDATE_VALUE_BANNER_CLOSED, true);
        return true;
      }

      throw resp;
    });
  },
  refreshPaypalStatus({ commit, getters }) {
    return ajax({
      url: getters.adminController,
      action: 'RefreshPaypalAccountStatus'
    }).then(paypalModule => {
      commit(types.UPDATE_PAYPAL_ACCOUNT_STATUS, paypalModule);
      return Promise.resolve();
    });
  }
};
