import Vue from 'vue';
import Vuex from 'vuex';
import firebase from './modules/firebase';
import paypal from './modules/paypal';
import configuration from './modules/configuration';

Vue.use(Vuex);

export default new Vuex.Store({
  modules: {
    firebase,
    paypal,
    configuration,
  },
});
