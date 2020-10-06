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
export class ExpressButtonCheckoutComponent {
  constructor(checkout) {
    this.checkout = checkout;
    this.checkoutConfig = checkout.config;

    this.htmlElementService = checkout.htmlElementService;
    this.payPalService = checkout.payPalService;
    this.psCheckoutService = checkout.psCheckoutService;

    this.$ = this.checkout.$;

    this.buttonContainer = this.htmlElementService.getCheckoutExpressCheckoutButtonContainer();
  }

  renderPayPalButton() {
    if (
      !this.payPalService
        .getEligibleFundingSources()
        .filter(({ name }) => name === 'paypal').length > 0
    )
      return;

    return this.payPalService
      .getButtonExpress('paypal', {
        onInit: (data, actions) => actions.enable(),
        onClick: (data, actions) =>
          this.psCheckoutService
            .postCheckCartOrder({ ...data, fundingSource: 'paypal', isExpressCheckout: true }, actions)
            // TODO: Error notification
            .catch(() => actions.reject()),
        // TODO: [PAYSHIP-605] Error handling
        onError: error => console.error(error),
        onApprove: data =>
          // TODO: Move this to constant when ExpressCheckoutButton component is created
          this.psCheckoutService.postExpressCheckoutOrder({
            ...data,
            fundingSource: 'paypal',
            isExpressCheckout: true
          }),
        onCancel: data =>
          this.psCheckoutService.postCancelOrder({
            ...data,
            fundingSource: 'paypal',
            isExpressCheckout: true
          }),
        createOrder: data =>
          this.psCheckoutService.postCreateOrder({
            ...data,
            fundingSource: 'paypal',
            isExpressCheckout: true
          })
      })
      .render('#ps-checkout-express-button');
  }

  renderTitle() {
    this.checkoutExpressTitle = document.createElement('ul');
    this.checkoutExpressTitle.classList.add('nav', 'nav-inline', 'my-1');

    this.checkoutExpressTitleItem = document.createElement('li');
    this.checkoutExpressTitleItem.classList.add('nav-item');

    this.checkoutExpressTitleItemHeading = document.createElement('div');
    this.checkoutExpressTitleItemHeading.classList.add('nav-link', 'active');
    this.checkoutExpressTitleItemHeading.innerText = this.$(
      'express-button.checkout.express-checkout'
    );

    this.checkoutExpressTitleItem.append(this.checkoutExpressTitleItemHeading);
    this.checkoutExpressTitle.append(this.checkoutExpressTitleItem);
  }

  render() {
    this.checkoutExpressButton = document.createElement('div');
    this.checkoutExpressButton.id = 'ps-checkout-express-button';

    this.renderTitle();

    this.buttonContainer.prepend(this.checkoutExpressButton);
    this.buttonContainer.prepend(this.checkoutExpressTitle);

    this.renderPayPalButton();
    return this;
  }
}
