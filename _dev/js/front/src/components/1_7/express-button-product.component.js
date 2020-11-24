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
export class ExpressButtonProductComponent {
  constructor(checkout) {
    this.checkout = checkout;
    this.checkoutConfig = checkout.config;

    this.htmlElementService = checkout.htmlElementService;
    this.payPalService = checkout.payPalService;
    this.psCheckoutService = checkout.psCheckoutService;

    this.buttonContainer = this.htmlElementService.getCheckoutExpressProductButtonContainer(
      true
    );
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
            // TODO: Move this to constant when ExpressCheckoutButton component is created
            .postCheckCartOrder(
              { ...data, fundingSource: 'paypal', isExpressCheckout: true },
              actions
            )
            // TODO: Error notification
            .catch(() => actions.reject()),
        // TODO: [PAYSHIP-605] Error handling
        onError: (error) => console.error(error),
        onApprove: (data, actions) =>
          this.psCheckoutService.postExpressCheckoutOrder(
            {
              ...data,
              fundingSource: 'paypal',
              isExpressCheckout: true
            },
            actions
          ),
        onCancel: (data) =>
          this.psCheckoutService.postCancelOrder({
            ...data,
            fundingSource: 'paypal',
            isExpressCheckout: true
          }),
        createOrder: () => {
          const {
            id_product,
            id_product_attribute,
            id_customization,
            quantity_wanted
          } = this.psCheckoutService.getProductDetails();

          return this.psCheckoutService.postCreateOrder({
            id_product,
            id_product_attribute,
            id_customization,
            quantity_wanted,
            fundingSource: 'paypal',
            isExpressCheckout: true
          });
        }
      })
      .render('#ps-checkout-express-button');
  }

  render() {
    this.checkoutExpressButton = document.createElement('div');
    this.checkoutExpressButton.id = 'ps-checkout-express-button';

    const productQuantityHTMLElement = this.buttonContainer.querySelector(
      '.product-quantity'
    ).nextElementSibling;

    this.buttonContainer.insertBefore(
      this.checkoutExpressButton,
      productQuantityHTMLElement
    );

    this.renderPayPalButton();
    return this;
  }
}
