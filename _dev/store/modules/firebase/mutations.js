import * as types from './mutation-types';

export default {
  [types.UPDATE_ACCOUNT](state, payload) {
    Object.assign(state.account, payload);
  },
  [types.LOGOUT_ACCOUNT](state) {
    state.account.email = '';
    state.account.localId = '';
    state.account.idToken = '';
    state.account.refreshToken = '';
    state.account.status = false;
  },
};
