import * as types from './mutation-types';

export default {
  [types.UNLINK_ACCOUNT](state) {
    state.account.idMerchant = '';
    state.account.emailMerchant = '';
    state.account.onboardingCompleted = false;
  },
  [types.UPDATE_ONBOARDING_LINK](state, onboardingLink) {
    state.account.paypalOnboardingLink = onboardingLink;
  },
};
