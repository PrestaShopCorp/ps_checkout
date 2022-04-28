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

    this.data.HTMLExpressCheckoutPaymentConfirmation = this.querySelectorService.getBasePaymentConfirmation();

    this.data.HTMLElementHookPayment = document.querySelector('#HOOK_PAYMENT');
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

  renderPaymentOptionListener() {
    const HTMLListenerElements = this.children.paymentOptions.map(
      (paymentOption) => {
        const HTMLElement = paymentOption.data.HTMLElementContainer;
        const [button, form] = Array.prototype.slice.call(
          HTMLElement.querySelectorAll('.payment_module')
        );

        return { button, form };
      }
    );

    this.children.paymentOptions.forEach((paymentOption, index) => {
      paymentOption.onLabelClick(() => {
        HTMLListenerElements.forEach(({ button, form }) => {
          button.classList.add('closed');
          form.classList.add('closed');
          button.classList.remove('open');
          form.classList.remove('open');

          this.data.notification.hideCancelled();
          this.data.notification.hideError();
        });

        HTMLListenerElements[index].button.classList.add('open');
        HTMLListenerElements[index].button.classList.remove('closed');
        HTMLListenerElements[index].form.classList.add('open');
        HTMLListenerElements[index].form.classList.remove('closed');
      });
    });
  }

  getHookPaymentElements() {
    return Array.prototype.slice.call(
      document.querySelector('#HOOK_PAYMENT').children
    );
  }

  renderExpressCheckoutPaymentButton() {
    const paymentButton = this.data.HTMLExpressCheckoutPaymentConfirmation;

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

    paymentButton.disabled = !this.data.conditions.isChecked();
    this.data.conditions.onChange(() => {
      paymentButton.disabled = !this.data.conditions.isChecked();
    });

    this.getHookPaymentElements().forEach((element) => {
      if (element.id !== 'ps_checkout-displayPayment' && element.id !== 'ps_checkout-notification-container') {
        element.style.display = 'none';
      }
    });
  }

  render() {
    this.data.conditions = this.app.root.children.conditionsCheckbox;
    this.data.notification = this.app.root.children.notification;
    this.data.loader = this.app.root.children.loader;

    if (!this.config.expressCheckout.active) {
      this.renderPaymentOptionItems();
      this.renderPaymentOptionListener();
    } else {
      this.data.HTMLElement.style.display = 'none';
      this.renderExpressCheckoutPaymentButton();
    }

    return this;
  }
}
