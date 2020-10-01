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
import { HtmlElementPs1_7Service } from '../../service/html-element-ps1_7.service';
import { PaypalService } from '../../service/paypal.service';
import { PsCheckoutService } from '../../service/ps-checkout.service';

import { ExpressButtonCartComponent } from '../1_7/express-button-cart.component';
import { ExpressButtonCheckoutComponent } from '../1_7/express-button-checkout.component';
import { ExpressButtonProductComponent } from '../1_7/express-button-product.component';
import { TranslationService } from '../../service/translation.service';

export class PsCheckoutExpressPs1_7Component {
  /**
   * @param {PsCheckoutConfig} config
   * @param {PayPalSdk} sdk
   */
  constructor(config, sdk) {
    this.config = config;
    this.sdk = sdk;

    this.translationService = new TranslationService(); // TODO: Get locale

    this.htmlElementService = new HtmlElementPs1_7Service();
    this.payPalService = new PaypalService(this.sdk, this.translationService);
    this.psCheckoutService = new PsCheckoutService(this.config);

    this.$ = id => this.translationService.getTranslationString(id);

    this.children = {};
  }

  render() {
    if (undefined === this.sdk) {
      throw new Error('No PayPal Javascript SDK Instance');
    }

    switch (document.body.id) {
      case 'cart':
        if (!this.config.expressCheckoutCartEnabled) return;
        if (document.body.classList.contains('cart-empty')) return;
        this.children.expressButton = new ExpressButtonCartComponent(
          this
        ).render();
        break;
      case 'checkout':
        if (!this.config.expressCheckoutOrderEnabled) return;
        this.children.expressButton = new ExpressButtonCheckoutComponent(
          this
        ).render();
        break;
      case 'product':
        if (!this.config.expressCheckoutProductEnabled) return;
        this.children.expressButton = new ExpressButtonProductComponent(
          this
        ).render();
        break;
    }
  }
}
