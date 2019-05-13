import * as types from './mutation-types';
import {ajax} from '@/requests/ajax.js';

export default {
  login({commit}, payload) {
    return new Promise((resolve, reject) => {
      ajax({
        action: 'SignIn',
        data: {
          email: payload.email,
          password: payload.password,
        },
      }).then((user) => {
        if (user.error) {
          reject(user);
        } else {
          commit(types.UPDATE_ACCOUNT, {
            email: user.email,
            idToken: user.idToken,
            localId: user.localId,
            refreshToken: user.refreshToken,
          });
        }
      });
    });
  },
};
