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

export class HostedFieldsComponent {
  constructor(
    checkout,
    paymentOption,
    fundingSource,
    hostedFieldsContainer,
    paymentButtonContainer
  ) {
    this.checkout = checkout;
    this.checkoutConfig = checkout.config;

    this.paymentOption = paymentOption;

    this.fundingSource = fundingSource;

    this.htmlElementService = checkout.htmlElementService;
    this.payPalService = checkout.payPalService;
    this.psCheckoutService = checkout.psCheckoutService;

    this.buttonContainer =
      paymentButtonContainer || this.htmlElementService.getButtonContainer();
    this.hostedFieldsContainer = hostedFieldsContainer;

    this.paymentOptionsContainer = this.htmlElementService.getPaymentOptionsContainer();

    this.validity = false;
  }

  getButtonId() {
    return `button-${this.fundingSource.name}`;
  }

  isSubmittable() {
    return this.checkout.children.conditionsCheckbox
      ? this.checkout.children.conditionsCheckbox.isChecked() && this.validity
      : this.validity;
  }

  render() {
    if (this.htmlElementService.getHostedFieldsForm) {
      this.hostedFieldForms = this.htmlElementService.getHostedFieldsForm();
      this.hostedFieldForms.style.display = 'block';
    }

    this.smartButton = document.createElement('div');

    this.smartButton.id = this.getButtonId();
    this.smartButton.classList.add(SMART_BUTTON_CLASS);

    this.hostedFieldSubmitButton = document
      .querySelector("#payment-confirmation [type='submit']")
      .cloneNode(true);

    this.hostedFieldSubmitButton.id = 'ps_checkout-hosted-submit-button';
    this.hostedFieldSubmitButton.type = 'button';
    this.hostedFieldSubmitButton.disabled = true;

    this.hostedFieldSubmitButton.classList.remove('disabled');
    this.checkout.children.conditionsCheckbox &&
      this.checkout.children.conditionsCheckbox.onChange(() => {
        this.hostedFieldSubmitButton.disabled = !this.isSubmittable();
      });

    this.smartButton.append(this.hostedFieldSubmitButton);
    this.buttonContainer.append(this.smartButton);

    this.payPalService
      .getHostedFields(
        {
          number: '#ps_checkout-hosted-fields-card-number',
          cvv: '#ps_checkout-hosted-fields-card-cvv',
          expirationDate: '#ps_checkout-hosted-fields-card-expiration-date'
        },
        {
          createOrder: () =>
            this.psCheckoutService
              .postCreateOrder({
                fundingSource: this.fundingSource.name,
                isHostedFields: true
              })
              .catch((error) => {
                this.checkout.children.notification.showError(
                  `${error.message} ${error.name}`
                );
              })
        }
      )
      .then((hostedFields) => {
        if (null !== this.hostedFieldForms) {
          const hostedFieldsSubmitButton = document.getElementById(
            this.hostedFieldSubmitButton.id
          );

          hostedFields.on('validityChange', (event) => {
            this.validity =
              Object.keys(event.fields)
                .map((name) => event.fields[name])
                .map(({ isValid }) => {
                  return isValid;
                })
                .filter((validity) => validity === false).length === 0;

            this.hostedFieldSubmitButton.disabled = !this.isSubmittable();
          });

          hostedFieldsSubmitButton.addEventListener('click', (event) => {
            event.preventDefault();
            this.checkout.children.loader.show();
            hostedFieldsSubmitButton.disabled = true;
            hostedFields
              .submit({
                contingencies: ['3D_SECURE']
              })
              .then((payload) => {
                const { liabilityShift } = payload;
                return this.psCheckoutService
                  .validateLiablityShift(liabilityShift)
                  .then(() => {
                    const data = payload;

                    // Backend requirement
                    data.orderID = data.orderId;
                    delete data.orderId;

                    return this.psCheckoutService.postValidateOrder({
                      ...data,
                      fundingSource: this.fundingSource.name,
                      isHostedFields: true
                    });
                  });
              })
              .catch((error) => {
                this.checkout.children.loader.hide();
                this.checkout.children.notification.showError(error.message);
                hostedFieldsSubmitButton.disabled = false;
              });
          });
        }
      });

    return this;
  }

  show() {
    this.smartButton.style.display = 'block';
  }

  hide() {
    this.smartButton.style.display = 'none';
  }
}
