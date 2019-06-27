import Vue from 'vue';
import i18n from './lib/i18n';
import App from './App.vue';
import router from './router';
import store from './store';
import './assets/css/compliant.css';
import './assets/css/ui-kit.css';
// TODO: Find a way to use the prestakit
// import '../node_modules/prestakit/dist/css/bootstrap-prestashop-ui-kit.css';

Vue.config.productionTip = process.env.NODE_ENV === 'production';

new Vue({
  router,
  store,
  i18n,
  render: h => h(App),
}).$mount('#app');
