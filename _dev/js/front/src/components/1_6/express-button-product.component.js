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

export class ExpressButtonProductComponent extends BaseComponent {
  static Inject = {
    querySelectorService: 'QuerySelectorService',
    psCheckoutApi: 'PsCheckoutApi',
    prestashopService: 'PrestashopService'
  };

  created() {
    this.buttonReferenceContainer = this.querySelectorService.getCheckoutExpressCheckoutButtonContainerProduct();
  }

  render() {
    this.checkoutExpressButton = document.createElement('p');
    this.checkoutExpressButton.id = 'ps-checkout-express-button';
    this.checkoutExpressButton.classList.add(
      'buttons_bottom_block',
      'no-print'
    );

    const buttonContainer = this.buttonReferenceContainer.parentNode;

    buttonContainer.append(this.checkoutExpressButton);

    this.children.expressCheckoutButton = new ExpressCheckoutButtonComponent(
      this.app,
      {
        // TODO: Move this to constant when ExpressCheckoutButton component is created
        querySelector: '#ps-checkout-express-button',
        createOrder: () => {
          const {
            id_product,
            id_product_attribute,
            id_customization,
            quantity_wanted
          } = this.prestashopService.getProductDetails();

          return this.psCheckoutApi.postCreateOrder({
            id_product,
            id_product_attribute,
            id_customization,
            quantity_wanted,
            fundingSource: 'paypal',
            isExpressCheckout: true
          });
        }
      }
    ).render();

    return this;
  }
}
