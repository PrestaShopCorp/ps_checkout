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
import 'promise-polyfill/src/polyfill';
import 'whatwg-fetch';
import 'url-polyfill';

import { PayPalSdkConfig } from './config/paypal-sdk.config';
import { PsCheckoutConfig } from './config/ps-checkout.config';

import { PayPalSdkComponent } from './components/paypal-sdk.component';
import { PsCheckoutExpressComponent } from './components/ps-checkout-express.component';
import { PsCheckoutComponent } from './components/ps-checkout.component';
import { bootstrap } from './core/bootstrap';
import { PsCheckoutService } from './service/ps-checkout.service';
import {
  PS_VERSION_1_6,
  PS_VERSION_1_7
} from './constants/ps-version.constants';

function isTokenNeeded() {
  if (!PsCheckoutConfig.expressCheckoutHostedFieldsEnabled) return false;

  switch (PsCheckoutService.getPrestashopVersion()) {
    case PS_VERSION_1_6: {
      if (document.body.id === 'order') {
        return !!document
          .getElementById('step_end')
          .classList.contains('step_current');
      }

      return document.body.id === 'order-opc';
    }
    case PS_VERSION_1_7: {
      if (document.body.id !== 'checkout') return false;
      return !document
        .getElementById('checkout-payment-step')
        .classList.contains('-unreachable');
    }
  }
}

bootstrap(() => {
  (isTokenNeeded()
    ? PayPalSdkConfig.clientToken
      ? Promise.resolve(PayPalSdkConfig.clientToken)
      : new PsCheckoutService(PsCheckoutConfig).postGetToken()
    : Promise.resolve('')
  )
    .then(token => {
      new PayPalSdkComponent(PayPalSdkConfig, token, sdk => {
        new PsCheckoutComponent(PsCheckoutConfig, sdk).render();
        new PsCheckoutExpressComponent(PsCheckoutConfig, sdk).render();
      }).render();
    })
    .catch(() => console.error('Token could not be retrieved'));
});
