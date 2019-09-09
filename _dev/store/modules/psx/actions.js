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
    }).then((response) => {
      commit(types.UPDATE_FORM_DATA, payload);
      return Promise.resolve(response);
    });
  },
  psxOnboarding({commit}, payload) {
    commit(types.UPDATE_ONBOARDING_STATUS, payload);
  },
};
