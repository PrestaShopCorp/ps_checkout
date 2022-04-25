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

export class PayLaterExpressButtonCartComponent extends BaseComponent {
  static Inject = {
    querySelectorService: 'QuerySelectorService',
    prestashopService: 'PrestashopService',
    psCheckoutApi: 'PsCheckoutApi',
    $: '$'
  };

  created() {
    this.buttonReferenceContainer = this.querySelectorService.getCheckoutExpressCheckoutButtonContainerCart();
  }

  renderComponent() {
    if (!document.getElementById('ps_checkout-express-button-cart')) {
      this.checkoutExpressButton = document.createElement('div');
      this.checkoutExpressButton.id = 'ps_checkout-express-button-cart';
      this.checkoutExpressButton.classList.add(
        'ps_checkout-express-button',
        'ps_checkout-express-button-cart'
      );

      const separatorText = document.createElement('div');
      separatorText.classList.add('ps_checkout-express-separator');
      separatorText.innerText = this.$('express-button.cart.separator');

      this.buttonReferenceContainer.append(separatorText);
      this.buttonReferenceContainer.append(this.checkoutExpressButton);
    }

    this.children.expressCheckoutButton = new ExpressCheckoutButtonComponent(
      this.app,
      {
        fundingSource: 'paylater',
        // TODO: Move this to constant when ExpressCheckoutButton component is created
        querySelector: '#ps_checkout-express-button-cart',
        createOrder: (data) =>
          this.psCheckoutApi.postCreateOrder({
            ...data,
            fundingSource: 'paylater',
            isExpressCheckout: true
          })
      }
    ).render();
  }

  render() {
    if (!this.buttonReferenceContainer) return;

    this.renderComponent();
    this.prestashopService.onUpdatedShoppingCartExtra(() =>
      this.renderComponent()
    );

    return this;
  }
}
