import * as types from './mutation-types';
import {ajax} from '@/requests/ajax.js';

export default {
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
