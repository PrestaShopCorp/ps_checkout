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
import { ExpressCheckoutButtonComponent } from '../common/express-checkout-button.component';

export class ExpressButtonCheckoutComponent extends BaseComponent {
  static Inject = {
    htmlElementService: 'HTMLElementService',
    PsCheckoutApi: 'PsCheckoutApi',
    $: '$'
  };

  getButtonContainer() {
    const selector = '#opc_account_form';
    return document.querySelector(selector);
  }

  created() {
    this.buttonContainer = this.getButtonContainer();
  }

  renderTitle() {
    this.checkoutExpressTitle = document.createElement('h3');
    this.checkoutExpressTitle.classList.add('page-heading', 'bottom-indent');
    this.checkoutExpressTitle.innerText = this.$(
      'express-button.checkout.express-checkout'
    );

    this.buttonContainer.prepend(this.checkoutExpressTitle);
  }

  render() {
    this.checkoutExpressButton = document.createElement('div');
    this.checkoutExpressButton.id = 'ps_checkout-express-button-checkout';
    this.checkoutExpressButton.classList.add(
      'ps_checkout-express-button',
      'ps_checkout-express-button-checkout'
    );

    this.children.expressCheckoutButton = new ExpressCheckoutButtonComponent(
      this.app,
      {
        querySelector: '#ps_checkout-express-button-checkout',
        createOrder: (data) =>
          this.psCheckoutApi.postCreateOrder({
            ...data,
            fundingSource: 'paypal',
            isExpressCheckout: true
          })
      }
    ).render();

    this.buttonContainer.prepend(this.checkoutExpressButton);

    this.renderTitle();

    return this;
  }
}
