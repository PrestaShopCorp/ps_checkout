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
    const HTMLListenerElements = this.children.paymentOptions.map((paymentOption) => {
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

        if (this.config.expressCheckout.active && (('ps_checkout-' + this.payPalService.getFundingSource()) !== HTMLListenerElements[index].button.dataset.moduleName)) {
          this.psCheckoutApi.postCancelOrder(
            {
              orderID: this.payPalService.getOrderId(),
              fundingSource: this.payPalService.getFundingSource(),
              isExpressCheckout: true
            }
          ).then(() => {
            this.config.expressCheckout.active = false;

            const expressCheckoutContainer = document.querySelector('#ps_checkout-express-checkout-banner');
            if (expressCheckoutContainer) {
              expressCheckoutContainer.style.display = 'none';
            }
          });
        }

        HTMLListenerElements[index].button.classList.add('open');
        HTMLListenerElements[index].button.classList.remove('closed');
        HTMLListenerElements[index].form.classList.add('open');
        HTMLListenerElements[index].form.classList.remove('closed');
      });
    });

    if (this.config.expressCheckout.active) {
      HTMLListenerElements.forEach(({ button, form }) => {
        if (button.dataset.moduleName === ('ps_checkout-' + this.payPalService.getFundingSource())) {
          button.classList.add('open');
          button.classList.remove('closed');
          form.classList.add('open');
          form.classList.remove('closed');
        }
      });
    }
  }

  render() {
    this.data.conditions = this.app.root.children.conditionsCheckbox;
    this.data.notification = this.app.root.children.notification;
    this.data.loader = this.app.root.children.loader;

    this.renderPaymentOptionItems();
    this.renderPaymentOptionListener();

    return this;
  }
}
