import * as types from './mutation-types';

export default {
  [types.UPDATE_ACCOUNT](state, payload) {
    Object.assign(state, payload);
  },
  [types.LOGOUT_ACCOUNT](state) {
    state.email = '';
    state.localId = '';
    state.idToken = '';
    state.refreshToken = '';
    state.onboardingCompleted = false;
  },
};
