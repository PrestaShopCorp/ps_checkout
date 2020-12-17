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
import { BaseComponent } from '../../core/dependency-injection/base.component';
import {
  PS_VERSION_1_6,
  PS_VERSION_1_7
} from '../../constants/ps-version.constants';

import { PsCheckoutPs1_6Component } from './ps-checkout-ps1_6.component';
import { PsCheckoutPs1_7Component } from './ps-checkout-ps1_7.component';

export class PsCheckoutComponent extends BaseComponent {
  static Inject = {
    prestashopService: 'PrestashopService'
  };

  created() {
    this.instance = new {
      [PS_VERSION_1_6]: PsCheckoutPs1_6Component,
      [PS_VERSION_1_7]: PsCheckoutPs1_7Component
    }[this.prestashopService.getVersion()](this.app, this.props);
  }

  render() {
    return this.instance.render();
  }
}
