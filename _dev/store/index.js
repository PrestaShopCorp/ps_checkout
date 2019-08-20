import Vue from 'vue';
import Vuex from 'vuex';
import firebase from './modules/firebase';
import paypal from './modules/paypal';
import configuration from './modules/configuration';
import context from './modules/context';
import psx from './modules/psx';

Vue.use(Vuex);

export default new Vuex.Store({
  modules: {
    context,
    firebase,
    paypal,
    configuration,
    psx,
  },
});
