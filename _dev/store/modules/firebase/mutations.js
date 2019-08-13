import * as types from './mutation-types';

export default {
  [types.LOGOUT_ACCOUNT](state) {
    state.email = '';
    state.localId = '';
    state.idToken = '';
    state.refreshToken = '';
    state.onboardingCompleted = false;
  },
};
