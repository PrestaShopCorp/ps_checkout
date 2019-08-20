import * as types from './mutation-types';

export default {
  [types.UPDATE_ONBOARDING_STATUS](state) {
    state.onboardingCompleted = '';
  },
  [types.UPDATE_FORM_DATA](state, payload) {
    state.psxFormData = payload;
  }
};
