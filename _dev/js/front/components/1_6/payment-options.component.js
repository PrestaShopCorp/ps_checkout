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
import { PaymentOptionComponent } from './payment-option.component';

export class PaymentOptionsComponent {
  constructor(checkout) {
    this.checkout = checkout;

    this.htmlElementService = checkout.htmlElementService;
    this.payPalService = checkout.payPalService;

    this.paymentOptionsContainer = this.htmlElementService.getPaymentOptionsContainer();
    this.paymentOptions = this.htmlElementService.getPaymentOptions();

    this.children = {};
  }

  onPaymentOptionChange(paymentOption) {
    this.children.paymentOptions.forEach(paymentOption => {
      if (!paymentOption.isDefaultPaymentOption()) {
        paymentOption.setOpen(false);
      }
    });

    paymentOption.setOpen(true);
  }

  render() {
    // Default Payment Options
    this.children.paymentOptions = this.paymentOptions.map(paymentOption =>
      new PaymentOptionComponent(this.checkout, null, paymentOption).render()
    );

    // PayPal Payment Options
    this.children.paymentOptions = [
      ...this.children.paymentOptions,
      ...this.payPalService.getEligibleFundingSources().map(fundingSource => {
        const paymentOption = new PaymentOptionComponent(
          this.checkout,
          fundingSource,
          null
        ).render();

        if (!paymentOption.isDefaultPaymentOption()) {
          paymentOption.onClick((...args) => {
            this.checkout.children.notification.hideCancelled();
            this.checkout.children.notification.hideError();
            this.onPaymentOptionChange(...args);
          });
        }

        return paymentOption;
      })
    ];

    return this;
  }
}
