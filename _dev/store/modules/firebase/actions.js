import * as types from './mutation-types';
import ajax from '@/requests/ajax.js';

export default {
  logout({commit, getters}) {
    return ajax({
      url: getters.adminController,
      action: 'FirebaseLogout',
    }).then(() => {
      commit(types.LOGOUT_ACCOUNT);
      return Promise.resolve(true);
    });
  },
};
