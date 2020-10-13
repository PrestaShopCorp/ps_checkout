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
import { SMART_BUTTON_CLASS } from '../../constants/ps-checkout-classes.constants';
import { PaymentOptionComponent } from './payment-option.component';

export class PaymentOptionsComponent {
  constructor(checkout) {
    this.checkout = checkout;
    this.config = this.checkout.config;

    this.htmlElementService = this.checkout.htmlElementService;
    this.payPalService = this.checkout.payPalService;
    this.psCheckoutService = this.checkout.psCheckoutService;

    this.paymentOptionsContainer = this.htmlElementService.getPaymentOptionsContainer();
    this.paymentOptions = this.htmlElementService.getPaymentOptions();

    this.buttonContainer = this.htmlElementService.getButtonContainer();

    this.children = {};
  }

  render() {
    if (!this.config.expressCheckoutSelected) {
      // Default Payment Options
      this.children.paymentOptions = this.paymentOptions.map(paymentOption =>
        new PaymentOptionComponent(this.checkout, null, paymentOption).render()
      );

      // PayPal Payment Options
      this.children.paymentOptions = [
        ...this.children.paymentOptions,
        ...this.payPalService
          .getEligibleFundingSources()
          .map(fundingSource =>
            new PaymentOptionComponent(
              this.checkout,
              fundingSource,
              null
            ).render()
          )
      ];
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

      paymentButton.addEventListener('click', event => {
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
          .catch(error => {
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
