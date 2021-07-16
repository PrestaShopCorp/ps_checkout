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

import { ExpressButtonCartComponent } from '../1_6/express-button-cart.component';
import { ExpressButtonCheckoutComponent } from '../1_6/express-button-checkout.component';
import { ExpressButtonProductComponent } from '../1_6/express-button-product.component';
import { ExpressCheckoutButtonComponent } from '../common/express-checkout-button.component';

export class PsCheckoutExpressPs1_6Component extends BaseComponent {
  static ID = 0;

  static Inject = {
    config: 'PsCheckoutConfig',
    prestashopService: 'PrestashopService',
    psCheckoutApi: 'PsCheckoutApi'
  };

  renderExpressCheckoutCustom() {
    this.props.HTMLElement.classList.add('ps_checkout-express-button');
    this.props.HTMLElement.setAttribute(
      'express-button-id',
      PsCheckoutExpressPs1_6Component.ID
    );

    this.children.expressButton = new ExpressCheckoutButtonComponent(this.app, {
      querySelector: `.ps_checkout-express-button[express-button-id="${PsCheckoutExpressPs1_6Component.ID++}"]`,
      createOrder: data =>
        this.psCheckoutApi.postCreateOrder({
          ...(this.props.productData || data),
          fundingSource: 'paypal',
          isExpressCheckout: true
        })
    }).render();
  }

  renderExpressCheckout() {
    if (this.props.HTMLElement) {
      this.renderExpressCheckoutCustom();
      return;
    }

    if (this.prestashopService.isCartPage()) {
      if (!this.config.expressCheckout.enabled.cart) return this;
      if (!this.prestashopService.hasProductInCart()) return this;

      this.children.expressButton = new ExpressButtonCartComponent(
        this.app
      ).render();

      return this;
    }

    if (this.prestashopService.isOrderPersonalInformationStepPage()) {
      if (!this.config.expressCheckout.enabled.order) return this;
      if (!this.prestashopService.hasProductInCart()) return this;
      this.children.expressButton = new ExpressButtonCheckoutComponent(
        this.app
      ).render();

      return this;
    }

    if (
      this.prestashopService.isProductPage() &&
      !this.prestashopService.isIframeProductPage()
    ) {
      if (!this.config.expressCheckout.enabled.product) return;
      if (
        this.children.expressButton &&
        this.children.expressButton.checkoutExpressButton &&
        this.children.expressButton.checkoutExpressButton.parentNode
      )
        return;

      this.children.expressButton = new ExpressButtonProductComponent(
        this.app
      ).render();

      return this;
    }
  }

  render() {
    this.renderExpressCheckout();

    return this;
  }
}
