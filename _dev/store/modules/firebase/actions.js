import * as types from './mutation-types';
import ajax from '@/requests/ajax.js';

export default {
  logOut({commit, getters}) {
    return ajax({
      url: getters.adminController,
      action: 'LogOut',
    }).then(() => {
      commit(types.LOGOUT_ACCOUNT);
      return Promise.resolve(true);
    });
  },

  signIn({commit, getters}, payload) {
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

  signUp({commit, getters}, payload) {
    return ajax({
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
