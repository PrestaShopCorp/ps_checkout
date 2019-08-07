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

  login({commit, getters}, payload) {
    return ajax({
      url: getters.adminController,
      action: 'SignIn',
      data: {
        email: payload.email,
        password: payload.password,
      },
    }).then((user) => {
      if (user.error) {
        return Promise.reject(user);
      }

      commit(types.UPDATE_ACCOUNT, {
        email: user.email,
        idToken: user.idToken,
        localId: user.localId,
        refreshToken: user.refreshToken,
        onboardingCompleted: true,
      });

      return Promise.resolve(user);
    });
  },

  signup({commit, getters}, payload) {
    ajax({
      url: getters.adminController,
      action: 'SignUp',
      data: {
        email: payload.email,
        password: payload.password,
      },
    }).then((user) => {
      if (user.error) {
        return Promise.reject(user);
      }

      commit(types.UPDATE_ACCOUNT, {
        email: user.email,
        idToken: user.idToken,
        localId: user.localId,
        refreshToken: user.refreshToken,
        onboardingCompleted: true,
      });

      return Promise.resolve(user);
    });
  },
};
