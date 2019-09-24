import * as types from './mutation-types';
import ajax from '@/requests/ajax.js';

export default {
  updateRoundingSettings({commit, getters}) {
    return ajax({
      url: getters.adminController,
      action: 'EditRoundingSettings',
    }).then((resp) => {
      if (resp) {
        commit(types.UPDATE_ROUNDING_SETTINGS_STATUS);
        return Promise.resolve(true);
      }

      return Promise.reject(resp);
    });
  },
};
