/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
import * as Sentry from '@sentry/vue';
import { Integrations } from '@sentry/tracing';

export default {
  install(Vue, { store }) {
    const correlationId = Math.random()
      .toString(36)
      .substr(2, 9);

    Sentry.init({
      Vue,
      dsn: `https://${process.env.VUE_APP_SENTRY_KEY}@${process.env.VUE_APP_SENTRY_ORGANIZATION}.ingest.sentry.io/${process.env.VUE_APP_SENTRY_PROJECT}`,
      integrations: [new Integrations.BrowserTracing()],

      // We recommend adjusting this value in production, or using tracesSampler
      // for finer control
      tracesSampleRate: 1.0,

      logErrors: process.env.NODE_ENV !== 'production'
    });

    Sentry.configureScope(scope => {
      scope.setExtras(store.state);
      scope.setTag('transaction_id', correlationId);
    });
  }
};
