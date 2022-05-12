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

import { PayLaterButtonCartComponent } from '../1_6/pay-later-button-cart.component';
import { PayLaterButtonCheckoutComponent } from '../1_6/pay-later-button-checkout.component';
import { PayLaterButtonProductComponent } from '../1_6/pay-later-button-product.component';
import { ExpressCheckoutButtonComponent } from '../common/express-checkout-button.component';

export class PsCheckoutPayLaterButtonPs1_6Component extends BaseComponent {
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
      PsCheckoutPayLaterButtonPs1_6Component.ID
    );

    this.children.expressButton = new ExpressCheckoutButtonComponent(this.app, {
      fundingSource: 'paylater',
      querySelector: `.ps_checkout-express-button[express-button-id="${PsCheckoutPayLaterButtonPs1_6Component.ID++}"]`,
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
      if (!this.config.expressCheckout.enabled.cart || !this.config.payLater.button.cart) return this;
      if (!window.ps_checkoutCartProductCount) return this;

      this.children.expressButton = new PayLaterButtonCartComponent(
        this.app
      ).render();

      return this;
    }

    if (this.prestashopService.isOrderPersonalInformationStepPage()) {
      if (!this.config.expressCheckout.enabled.order || !this.config.payLater.button.order) return this;
      if (!window.ps_checkoutCartProductCount) return this;
      this.children.expressButton = new PayLaterButtonCheckoutComponent(
        this.app
      ).render();

      return this;
    }

    if (
      this.prestashopService.isProductPage() &&
      !this.prestashopService.isIframeProductPage()
    ) {
      if (!this.config.expressCheckout.enabled.product|| !this.config.payLater.button.product) return;
      if (
        this.children.expressButton &&
        this.children.expressButton.checkoutExpressButton &&
        this.children.expressButton.checkoutExpressButton.parentNode
      )
        return;

      this.children.expressButton = new PayLaterButtonProductComponent(
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
