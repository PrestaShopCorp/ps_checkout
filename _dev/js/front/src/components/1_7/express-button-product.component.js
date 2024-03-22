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

const BUTTON_CONTAINER_SELECTOR = 'ps-checkout-express-button';

export class ExpressButtonProductComponent extends BaseComponent {
  static Inject = {
    querySelectorService: 'QuerySelectorService',
    psCheckoutApi: 'PsCheckoutApi',
    prestashopService: 'PrestashopService'
  };

  created() {
    this.buttonReferenceContainer =
      this.querySelectorService.getExpressCheckoutButtonContainerProduct();
  }

  render() {
    this.checkoutExpressButton = document.createElement('div');
    this.checkoutExpressButton.id = BUTTON_CONTAINER_SELECTOR;

    const productQuantityHTMLElement =
      this.buttonReferenceContainer.nextElementSibling;

    this.buttonReferenceContainer.parentNode.insertBefore(
      this.checkoutExpressButton,
      productQuantityHTMLElement
    );

    this.updateButtonContainerVisibility();

    this.prestashopService.onUpdatedProduct(() => {
      this.updateButtonContainerVisibility();
    });

    this.children.expressCheckoutButton = new ExpressCheckoutButtonComponent(
      this.app,
      {
        fundingSource: 'paypal',
        querySelector: `#${BUTTON_CONTAINER_SELECTOR}`
      }
    ).render();

    return this;
  }

  updateButtonContainerVisibility() {
    if (this.prestashopService.isAddToCartButtonDisabled()) {
      document
        .getElementById(BUTTON_CONTAINER_SELECTOR)
        .classList.add('disabled');
    } else {
      document
        .getElementById(BUTTON_CONTAINER_SELECTOR)
        .classList.remove('disabled');
    }
  }
}
