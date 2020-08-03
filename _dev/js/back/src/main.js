/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
import Vue from "vue";
import BootstrapVue from "bootstrap-vue";
import VueCollapse from "vue2-collapse";
import * as Sentry from "@sentry/browser";
import { Vue as VueIntegration } from "@sentry/integrations";

import i18n from "./lib/i18n";
import App from "./App.vue";
import router from "./router";
import store from "./store";

Vue.use(BootstrapVue);
Vue.use(VueCollapse);
Sentry.init({
  dsn: `https://${process.env.VUE_APP_SENTRY_KEY}@${process.env.VUE_APP_SENTRY_ORGANIZATION}.ingest.sentry.io/${process.env.VUE_APP_SENTRY_PROJECT}`,
  integrations: [new VueIntegration({ Vue, attachProps: true })]
});
Sentry.configureScope(scope => {
  scope.setExtra(store.state);
});

const correlationId = Math.random()
  .toString(36)
  .substr(2, 9);
Sentry.configureScope(scope => {
  scope.setTag("transaction_id", correlationId);
});

Vue.config.productionTip = process.env.NODE_ENV === "production";

window.onload = () => {
  new Vue({
    router,
    store,
    i18n,
    render: h => h(App)
  }).$mount("#app");
};
