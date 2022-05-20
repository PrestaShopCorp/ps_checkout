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

export class PayLaterButtonCheckoutComponent extends BaseComponent {
  static Inject = {
    querySelectorService: 'QuerySelectorService',
    psCheckoutApi: 'PsCheckoutApi',
    $: '$'
  };

  created() {
    this.buttonReferenceContainer = this.querySelectorService.getExpressCheckoutButtonContainerCheckout();
  }

  renderTitle() {
    this.checkoutExpressTitle = document.createElement('ul');
    this.checkoutExpressTitle.classList.add('nav', 'nav-inline', 'my-1');

    this.checkoutExpressTitleItem = document.createElement('li');
    this.checkoutExpressTitleItem.classList.add('nav-item');

    this.checkoutExpressTitleItemHeading = document.createElement('div');
    this.checkoutExpressTitleItemHeading.classList.add('nav-link', 'active');
    this.checkoutExpressTitleItemHeading.innerText = this.$(
      'express-button.checkout.express-checkout'
    );

    this.checkoutExpressTitleItem.append(this.checkoutExpressTitleItemHeading);
    this.checkoutExpressTitle.append(this.checkoutExpressTitleItem);
  }

  render() {
    if (!document.getElementById('ps-checkout-express-button')) {
      this.checkoutExpressButton = document.createElement('div');
      this.checkoutExpressButton.id = 'ps-checkout-express-button';

      this.renderTitle();

      this.buttonReferenceContainer.prepend(this.checkoutExpressButton);
      this.buttonReferenceContainer.prepend(this.checkoutExpressTitle);
    }

    this.children.expressCheckoutButton = new ExpressCheckoutButtonComponent(
      this.app,
      {
        fundingSource: 'paylater',
        // TODO: Move this to constant when ExpressCheckoutButton component is created
        querySelector: '#ps-checkout-express-button',
        createOrder: (data) =>
          this.psCheckoutApi.postCreateOrder({
            ...data,
            fundingSource: 'paylater',
            isExpressCheckout: true
          })
      }
    ).render();
    return this;
  }
}
