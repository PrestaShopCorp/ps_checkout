import * as types from './mutation-types';

export default {
  [types.UNLINK_ACCOUNT](state) {
    state.idMerchant = '';
    state.emailMerchant = '';
    state.onboardingCompleted = false;
  },
  [types.UPDATE_ONBOARDING_LINK](state, onboardingLink) {
    state.paypalOnboardingLink = onboardingLink;
  },
};
