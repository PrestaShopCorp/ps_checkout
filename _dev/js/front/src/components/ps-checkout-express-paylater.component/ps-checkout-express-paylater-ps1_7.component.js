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

import { PayLaterExpressButtonCartComponent } from '../1_7/paylater-express-button-cart.component';
import { PayLaterExpressButtonCheckoutComponent } from '../1_7/paylater-express-button-checkout.component';
import { PayLaterExpressButtonProductComponent } from '../1_7/paylater-express-button-product.component';
import { ExpressCheckoutButtonComponent } from '../common/express-checkout-button.component';

export class PsCheckoutExpressPayLaterPs1_7Component extends BaseComponent {
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
      PsCheckoutExpressPayLaterPs1_7Component.ID
    );

    this.children.expressButton = new ExpressCheckoutButtonComponent(this.app, {
      fundingSource: 'paylater',
      querySelector: `.ps_checkout-express-button[express-button-id="${PsCheckoutExpressPayLaterPs1_7Component.ID++}"]`,
      createOrder: (data) =>
        this.psCheckoutApi.postCreateOrder({
          ...(this.props.productData || data),
          fundingSource: 'paylater',
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
      if (!this.config.expressCheckout.enabled.cart || !this.config.paylater.enabled.cart) return this;
      if (document.body.classList.contains('cart-empty')) return this;

      this.children.expressButton = new PayLaterExpressButtonCartComponent(
        this.app
      ).render();

      return this;
    }

    if (this.prestashopService.isOrderPersonalInformationStepPage()) {
      if (!this.config.expressCheckout.enabled.order || !this.config.paylater.enabled.order) return this;
      this.children.expressButton = new PayLaterExpressButtonCheckoutComponent(
        this.app
      ).render();

      return this;
    }

    if (this.prestashopService.isProductPage()) {
      if (!this.config.expressCheckout.enabled.product || !this.config.paylater.enabled.product) return;
      if (
        this.children.expressButton &&
        this.children.expressButton.checkoutExpressButton &&
        this.children.expressButton.checkoutExpressButton.parentNode
      )
        return;

      this.children.expressButton = new PayLaterExpressButtonProductComponent(
        this.app
      ).render();

      return this;
    }
  }

  render() {
    this.renderExpressCheckout();
    this.prestashopService.onUpdatedCart(() => {
      return this.renderExpressCheckout();
    });

    return this;
  }
}
