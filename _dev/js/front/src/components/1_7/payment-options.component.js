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
import { PaymentOptionComponent } from '../common/payment-option.component';

export class PaymentOptionsComponent extends BaseComponent {
  static Inject = {
    config: 'PsCheckoutConfig',
    payPalService: 'PayPalService',
    psCheckoutApi: 'PsCheckoutApi',
    querySelectorService: 'QuerySelectorService'
  };

  created() {
    this.data.HTMLElement = this.querySelectorService.getPaymentOptions();

    this.data.HTMLBasePaymentConfirmation = this.querySelectorService.getBasePaymentConfirmation();

    this.data.HTMLElementPayPalButton = document.querySelector(
      '.ps_checkout-button[data-funding-source="paypal"]'
    );
    this.data.HTMLElementPayPalRadio = document.querySelector(
      'input[type="radio"][data-module-name="ps_checkout-paypal"]'
    );
  }

  renderPaymentOptionItems() {
    this.children.paymentOptions = this.payPalService
      .getEligibleFundingSources()
      .map((fundingSource) => {
        const HTMLElement = document.querySelector(
          `[data-module-name^="ps_checkout-${fundingSource.name}"]`
        );

        return (
          HTMLElement &&
          new PaymentOptionComponent(this.app, {
            fundingSource: fundingSource,
            markPosition: this.props.markPosition,

            HTMLElement
          }).render()
        );
      })
      .filter((paymentOption) => paymentOption);
  }

  renderPaymentOptionRadios() {
    const radios = this.querySelectorService.getPaymentOptionRadios();
    radios.forEach((radio) => {
      radio.addEventListener('change', () => {
        this.data.notification.hideCancelled();
        this.data.notification.hideError();
      });
    });
  }

  renderExpressCheckoutPaymentButton() {
    const paymentButton = this.data.HTMLBasePaymentConfirmation.cloneNode(true);

    paymentButton.id = 'ps_checkout-hosted-submit-button';
    paymentButton.type = 'button';

    paymentButton.addEventListener('click', (event) => {
      event.preventDefault();
      this.data.loader.show();

      this.psCheckoutApi
        .postCheckCartOrder(
          {
            orderID: this.payPalService.getOrderId(),
            fundingSource: this.payPalService.getFundingSource(),
            isExpressCheckout: true
          },
          { resolve: () => {}, reject: () => {} }
        )
        .then(() =>
          this.psCheckoutApi.postValidateOrder({
            orderID: this.payPalService.getOrderId(),
            fundingSource: this.payPalService.getFundingSource(),
            isExpressCheckout: true
          })
        )
        .catch((error) => {
          console.log(error);
          this.data.loader.hide();
          this.data.notification.showError(error.message);
        });
    });

    this.children.expressCheckoutButton = document.createElement('div');

    this.children.expressCheckoutButton.id = 'button-paypal';
    this.children.expressCheckoutButton.classList.add(
      '.ps_checkout-express-checkout-button'
    );

    paymentButton.disabled = !this.data.conditions.isChecked();
    paymentButton.classList.remove('disabled');

    this.data.conditions.onChange(() => {
      setTimeout(() => {
        paymentButton.disabled = !this.data.conditions.isChecked();
      }, 0);
    });

    this.children.expressCheckoutButton.append(paymentButton);
    this.data.HTMLElementPayPalButton.append(
      this.children.expressCheckoutButton
    );
  }

  render() {
    this.data.conditions = this.app.root.children.conditionsCheckbox;
    this.data.notification = this.app.root.children.notification;
    this.data.loader = this.app.root.children.loader;

    if (!this.config.expressCheckout.active) {
      this.renderPaymentOptionItems();
      this.renderPaymentOptionRadios();
    } else {
      this.data.HTMLElement.style.display = 'none';
      this.data.HTMLElementPayPalRadio.checked = true;

      this.renderExpressCheckoutPaymentButton();
    }

    return this;
  }
}
