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

import ajax from '@/requests/ajax.js';
import ajaxWebhook from '@/requests/webhook';
import * as types from './mutation-types';

export default {
  openOnboardingSession({ commit, getters }, payload) {
    return ajax({
      url: getters.adminController,
      action: 'OpenOnboardingSession',
      data: {
        sessionData: JSON.stringify(payload.sessionData)
      }
    }).then(response => {
      if (!response.status) {
        throw response;
      }

      commit(types.ONBOARDING_SESSION, response);

      return Promise.resolve(response);
    });
  },
  pollingPaypalOnboardingUrl({ getters, dispatch }) {
    const canBeActivated = () =>
      getters.firebaseOnboardingIsCompleted && !getters.hasOnboardingUrl;
    dispatch('getOpenedOnboardingSession').then(() => {
      if (!canBeActivated()) {
        return;
      }
      let time = 0;
      let poll = setInterval(() => {
        time++;

        if (!canBeActivated() || time >= 30) {
          clearInterval(poll);
          poll = setInterval(() => {
            if (!canBeActivated()) {
              clearInterval(poll);
            } else {
              dispatch('getOpenedOnboardingSession');
            }
          }, 30000);
        } else {
          dispatch('getOpenedOnboardingSession');
        }
      }, 1000);
    });
  },
  transitOnboardingSession({ commit, getters }, payload) {
    return ajax({
      url: getters.adminController,
      action: 'TransitOnboardingSession',
      data: {
        sessionAction: payload.sessionAction,
        session: JSON.stringify(payload.session)
      }
    }).then(response => {
      if (!response.status) {
        throw response;
      }

      commit(types.ONBOARDING_SESSION, response);

      return Promise.resolve(response);
    });
  },
  sendSseOnboardingWebhook({ getters, state }, { data }) {
    const { correlation_id: correlationId } = state.onboarding;
    return ajaxWebhook({
      url: getters.webhookController,
      data: data,
      headers: {
        // PrestaShop Core does not understand well 'application/json'
        Accept: '*/*',
        'Correlation-Id': correlationId
      }
    });
  },
  closeOnboardingSession({ commit, getters }, payload) {
    return ajax({
      url: getters.adminController,
      action: 'CloseOnboardingSession',
      data: {
        session: JSON.stringify(payload.session)
      }
    }).then(response => {
      commit(types.ONBOARDING_SESSION, response);

      return Promise.resolve(response);
    });
  },
  getOpenedOnboardingSession({ commit, getters }) {
    return ajax({
      url: getters.adminController,
      action: 'GetOpenedOnboardingSession'
    }).then(response => {
      commit(types.ONBOARDING_SESSION, response);

      return Promise.resolve(response);
    });
  }
};
