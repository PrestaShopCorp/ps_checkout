import * as types from './mutation-types';
import ajax from '@/requests/ajax.js';

export default {
  psxSendData({commit, getters}, payload) {
    return ajax({
      url: getters.adminController,
      action: 'PsxSendData',
      data: {
        payload: JSON.stringify(payload),
      },
    }).then(() => {
      commit(types.UPDATE_FORM_DATA, payload);
      return Promise.resolve(true);
    });
  },
  psxOnboarding({commit, getters}, payload) {
    return ajax({
      url: getters.adminController,
      action: 'PsxOnboarding',
      data: {
        status: payload,
      },
    }).then((data) => {
      commit(types.UPDATE_ONBOARDING_STATUS, data);
      return Promise.resolve(true);
    });
  },
};
