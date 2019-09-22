import * as types from './mutation-types';
import ajax from '@/requests/ajax.js';

export default {
  unlink({commit, getters}) {
    return ajax({
      url: getters.adminController,
      action: 'LogOutPaypalAccount',
    }).then(() => {
      commit(types.UNLINK_ACCOUNT);
      return Promise.resolve(true);
    });
  },
  getOnboardingLink({commit, getters}) {
    return ajax({
      url: getters.adminController,
      action: 'GetOnboardingLink',
    }).then((onboardingLink) => {
      commit(types.UPDATE_ONBOARDING_LINK, onboardingLink);
      return Promise.resolve(true);
    });
  },
};
