import Vue from 'vue';
import Vuex from 'vuex';
import firebase from './modules/firebase';

Vue.use(Vuex);

const paypalOnboardingLink = global.paypalOnboardingLink;

export default new Vuex.Store({
  state: {
    paypalOnboardingLink,
  },
  modules: {
    firebase,
  },
});
