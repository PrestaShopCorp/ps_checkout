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

/**
 * @typedef PaymentTokenComponentProps
 *
 * @param {string} fundingSource.name
 *
 * @param {HTMLElement} [HTMLElement]
 * @param {HTMLElement} HTMLElementWrapper
 */

export class PaymentTokenComponent extends BaseComponent {
  static Inject = {
    config: 'PsCheckoutConfig',
    payPalService: 'PayPalService',
    psCheckoutApi: 'PsCheckoutApi',
    querySelectorService: 'QuerySelectorService',
  };

  created() {
    this.data.name = this.props.fundingSource.name;
    this.data.orderId = this.payPalService.getOrderId();

    this.data.HTMLElement = this.props.HTMLElement;

    this.data.conditions = this.app.root.children.conditionsCheckbox;
    this.data.loader = this.app.root.children.loader;
    this.data.notification = this.app.root.children.notification;
    this.data.HTMLElementBaseButton =
      this.querySelectorService.getBasePaymentConfirmation();
    this.data.HTMLElementButton = null;
    this.data.HTMLElementButtonWrapper = this.getButtonWrapper();
  }

  getButtonWrapper() {
    const buttonWrapper = `.ps_checkout-button[data-funding-source=${this.data.name}]`;
    return document.querySelector(buttonWrapper);
  }

  handleError(error) {
    this.data.loader.hide();
    this.data.notification.showError(error.message);
    this.data.HTMLElementButton.removeAttribute('disabled');
  }

  submitOrder() {
    this.createOrder().then(() => this.validateOrder());
  }

  createOrder() {
    return this.psCheckoutApi.postCreateOrder(
      {
        fundingSource: this.data.name
      }
    )
      .then((data) => {
        this.data.orderId = data;
        this.validateOrder();
      })
      .catch((error) => this.handleError(error));
  }

  validateOrder() {
    this.psCheckoutApi.postValidateOrder(
      {
        orderID: this.data.orderId,
        fundingSource: this.data.name,
      }
    ).then(() => {
      this.data.loader.hide();
      this.data.HTMLElementButton.removeAttribute('disabled');
    }).catch((error) => this.handleError(error));
  }


  renderButton() {
    this.data.HTMLElementButton =
      this.data.HTMLElementBaseButton.cloneNode(true);

    this.data.HTMLElementButtonWrapper.append(this.data.HTMLElementButton);
    this.data.HTMLElementButton.classList.remove('disabled');
    this.data.HTMLElementButton.style.display = '';
    this.data.HTMLElementButton.disabled = !this.isSubmittable();

    this.data.conditions &&
    this.data.conditions.onChange(() => {
      // In some PS versions, the handler fails to disable the button because of the timing.
      setTimeout(() => {
        this.data.HTMLElementButton.disabled = !this.isSubmittable();
      }, 0);
    });

    this.data.HTMLElementButton.addEventListener('click', (event) => {
      event.preventDefault();

      this.data.loader.show();
      this.data.HTMLElementButton.setAttribute('disabled', '');

      this.submitOrder();
    });
  }



  isSubmittable() {
    return this.data.conditions ? this.data.conditions.isChecked() : false;
  }

  render() {
    this.renderButton();
    return this;
  }
}
