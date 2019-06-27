import * as types from './mutation-types';
import {ajax} from '@/requests/ajax.js';

export default {
  logout({commit}) {
    return ajax({
      action: 'FirebaseLogout',
    }).then(() => {
      commit(types.LOGOUT_ACCOUNT);
      return Promise.resolve(true);
    });
  },

  login({commit}, payload) {
    return ajax({
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
      });

      return Promise.resolve(user);
    });
  },

  signup({commit}, payload) {
    ajax({
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
      });

      return Promise.resolve(user);
    });
  },
};
