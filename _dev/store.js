import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

const trans = JSON.parse(translations);
const firebase = JSON.parse(firebaseAccount);

export default new Vuex.Store({
  state: {
    trans,
    firebase,
  },
  mutations: {
    updateFirebaseAccount(state, payload) {
      Object.assign(state, payload);
    },
  },
  actions: {
    updateFirebaseAccount({commit}, payload) {
      commit('updateFirebaseAccount', payload);
    },
  },
});
