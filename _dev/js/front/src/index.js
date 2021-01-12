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
import './utils/polyfills';
import './utils/globals';

import { App } from './core/app';
import { bootstrap } from './core/bootstrap';

bootstrap(async () => {
  window.ps_checkout = window.ps_checkout || {};
  if (window.ps_checkout.app) {
    return console.error(
      'There is an existing instance of `ps_checkout` on this context.'
    );
  }

  window.ps_checkout.app = new App();
  window.ps_checkout.events.dispatchEvent(
    new CustomEvent('init', { detail: { ps_checkout: window.ps_checkout } })
  );
  await window.ps_checkout.app.render();
  window.ps_checkout.events.dispatchEvent(
    new CustomEvent('loaded', { detail: { ps_checkout: window.ps_checkout } })
  );
});
