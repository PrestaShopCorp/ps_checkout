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
import {
  PS_VERSION_1_6,
  PS_VERSION_1_7
} from '../../constants/ps-version.constants';

import { PsCheckoutService } from '../../service/ps-checkout.service';
import { PsCheckoutExpressPs1_6Component } from './ps-checkout-express-ps1_6.component';
import { PsCheckoutExpressPs1_7Component } from './ps-checkout-express-ps1_7.component';

export class PsCheckoutExpressComponent {
  constructor(config, sdk) {
    this.instance = new {
      [PS_VERSION_1_6]: PsCheckoutExpressPs1_6Component,
      [PS_VERSION_1_7]: PsCheckoutExpressPs1_7Component
    }[PsCheckoutService.getPrestashopVersion()](config, sdk);
  }

  render() {
    this.instance.render();
  }
}
