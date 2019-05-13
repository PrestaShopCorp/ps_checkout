import Vue from 'vue';
import Vuex from 'vuex';
import firebase from './modules/firebase';

Vue.use(Vuex);

const trans = JSON.parse(translations);

export default new Vuex.Store({
  state: {
    trans,
  },
  modules: {
    firebase,
  },
});
