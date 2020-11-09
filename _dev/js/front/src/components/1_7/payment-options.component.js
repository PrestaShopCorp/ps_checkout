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
import { BaseComponent } from '../../core/base.component';

import { PaymentOptionComponent } from '../common/payment-option.component';
import { SMART_BUTTON_CLASS } from '../../constants/ps-checkout-classes.constants';

export class PaymentOptionsComponent extends BaseComponent {
  static INJECT = {
    config: 'config',
    htmlElementService: 'htmlElementService',
    payPalService: 'payPalService',
    psCheckoutService: 'psCheckoutService'
  };

  constructor(app, props) {
    super(app, props);

    // this.buttonContainer = this.htmlElementService.getButtonContainer();

    this.data.HTMLElement = this.getPaymentOptions();

    this.data.notificationComponent = this.app.children.notification;
  }

  getPaymentOptions() {
    const paymentOptionsSelector = '.payment-options';
    return document.querySelector(paymentOptionsSelector);
  }

  renderPaymentOptionItems() {
    this.children.paymentOptions = this.payPalService
      .getEligibleFundingSources()
      .map((fundingSource) =>
        new PaymentOptionComponent(this.app, {
          fundingSource: fundingSource,
          markPosition: this.props.markPosition,

          // TODO: Move this to HTMLElementService,
          HTMLElement: document.querySelector(
            `[data-module-name="ps_checkout-${fundingSource.name}"]`
          )
        }).render()
      );
  }

  renderPaymentOptionRadios() {
    const radios = Array.prototype.slice.call(
      this.data.HTMLElement.querySelectorAll(
        'input[type="radio"][name="payment-option"]'
      )
    );

    radios.forEach((radio) => {
      radio.addEventListener('change', () => {
        this.data.notificationComponent.hideCancelled();
        this.data.notificationComponent.hideError();
      });
    });
  }

  render() {
    if (!this.config.expressCheckoutSelected) {
      this.renderPaymentOptionItems();
      this.renderPaymentOptionRadios();
    } else {
      this.htmlElementService.getPaymentOptionsContainer().style.display =
        'none';

      this.htmlElementService.getAnyPaymentOption().checked = true;

      this.smartButton = document.createElement('div');

      this.smartButton.id = 'button-paypal';
      this.smartButton.classList.add(SMART_BUTTON_CLASS);

      const paymentButton = document
        .querySelector("#payment-confirmation [type='submit']")
        .cloneNode(true);

      paymentButton.id = 'ps_checkout-hosted-submit-button';
      paymentButton.type = 'button';

      paymentButton.addEventListener('click', (event) => {
        event.preventDefault();
        this.checkout.children.loader.show();

        this.psCheckoutService
          .postCheckCartOrder(
            {
              orderID: this.payPalService.getOrderId(),
              fundingSource: 'paypal',
              isExpressCheckout: true
            },
            { resolve: () => {}, reject: () => {} }
          )
          .then(() => {
            return this.psCheckoutService.postValidateOrder({
              orderID: this.payPalService.getOrderId(),
              fundingSource: 'paypal',
              isExpressCheckout: true
            });
          })
          .catch((error) => {
            console.log(error);
            this.checkout.children.loader.hide();
            this.checkout.children.notification.showError(error.message);
          });
      });

      this.checkout.children.conditionsCheckbox.onChange(() => {
        paymentButton.disabled = !this.checkout.children.conditionsCheckbox.isChecked();
      });

      this.smartButton.append(paymentButton);
      this.buttonContainer.append(this.smartButton);
    }

    return this;
  }
}
