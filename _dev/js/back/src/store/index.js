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
import Vue from 'vue';
import Vuex from 'vuex';

import firebase from './modules/firebase';
import paypal from './modules/paypal';
import configuration from './modules/configuration';
import context from './modules/context';
import psx from './modules/psx';
import session from './modules/session';

Vue.use(Vuex);

export default new Vuex.Store({
  modules: {
    context,
    firebase,
    paypal,
    configuration,
    psx,
    session
  }
});
