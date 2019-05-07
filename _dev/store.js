import Vue from 'vue';
import Vuex from 'vuex';
import {request} from '@/requests/ajax.js';

Vue.use(Vuex);

const trans = JSON.parse(translations);
const test = JSON.parse(firebaseAccount);

export default new Vuex.Store({
  state: {
    trans,
    account: {
      firebase: test,
    },
  },
  mutations: {
    updateFirebaseAccount(state, payload) {
      Vue.set(state.account.firebase, 'uid', payload.uid);
      Vue.set(state.account.firebase, 'refreshToken', payload.refreshToken);
    },
  },
  actions: {
    updateFirebaseAccount({commit}, payload) {
      request({
        action: 'SaveFirebaseToken',
        data: {
          uid: payload.uid,
          refreshToken: payload.refreshToken,
        },
      }).then(() => {
        commit('updateFirebaseAccount', payload);
      }).catch((err) => {
        console.log(err);
      });
    },
  },
});
