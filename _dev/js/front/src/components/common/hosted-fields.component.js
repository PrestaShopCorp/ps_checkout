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

export class HostedFieldsComponent extends BaseComponent {
  static INJECT = {
    config: 'config',
    htmlElementService: 'htmlElementService',
    payPalService: 'payPalService',
    psCheckoutService: 'psCheckoutService'
  };

  constructor(app, props) {
    super(app, props);

    this.data.name = props.fundingSource.name;
    this.data.validity = false;

    this.data.HTMLElement = props.HTMLElement;
    this.data.HTMLElementBaseButton = this.getBaseButton();
    this.data.HTMLElementButton = null;
    this.data.HTMLElementButtonWrapper = this.getButtonWrapper();
    this.data.HTMLElementCardNumber = this.getCardNumber();
    this.data.HTMLElementCardCVV = this.getCardCVV();
    this.data.HTMLElementCardExpirationDate = this.getCardExpirationDate();
    this.data.HTMLElementSection = this.getSection();

    this.data.conditionsComponent = this.app.children.conditionsCheckbox;
  }

  getBaseButton() {
    const buttonSelector = `#payment-confirmation button`;
    return document.querySelector(buttonSelector);
  }

  getButtonWrapper() {
    const buttonWrapper = `.ps_checkout-button[data-funding-source=${this.data.name}]`;
    return document.querySelector(buttonWrapper);
  }

  getCardNumber() {
    const cardNumberId = '#ps_checkout-hosted-fields-card-number';
    return document.getElementById(cardNumberId);
  }

  getCardCVV() {
    const cardCVVId = '#ps_checkout-hosted-fields-card-cvv';
    return document.getElementById(cardCVVId);
  }

  getCardExpirationDate() {
    const cardExpirationDateId =
      '#ps_checkout-hosted-fields-card-expiration-date';
    return document.getElementById(cardExpirationDateId);
  }

  getSection() {
    const sectionSelector = `.js-payment-ps_checkout-${this.data.name}`;
    return document.querySelector(sectionSelector);
  }

  isSubmittable() {
    return this.data.conditionsComponent
      ? this.data.conditionsComponent.isChecked() && this.data.validity
      : this.data.validity;
  }

  renderPayPalHostedFields() {
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
                fundingSource: this.data.name,
                isHostedFields: true
              })
              .catch((error) => {
                this.app.children.notification.showError(
                  `${error.message} ${error.name}`
                );
              })
        }
      )
      .then((hostedFields) => {
        if (this.data.HTMLElement !== null) {
          hostedFields.on('validityChange', (event) => {
            this.data.validity =
              Object.keys(event.fields)
                .map((name) => event.fields[name])
                .map(({ isValid }) => {
                  return isValid;
                })
                .filter((validity) => validity === false).length === 0;

            this.data.HTMLElementSection.classList.toggle(
              'disabled',
              !this.isSubmittable()
            );

            this.isSubmittable()
              ? this.data.HTMLElementButton.removeAttribute('disabled')
              : this.data.HTMLElementButton.setAttribute('disabled', '');
          });

          this.data.HTMLElementButton.addEventListener('click', (event) => {
            event.preventDefault();
            this.app.children.loader.show();
            this.data.HTMLElementSection.classList.toggle('disabled', true);

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
                      fundingSource: this.data.name,
                      isHostedFields: true
                    });
                  });
              })
              .catch((error) => {
                this.app.children.loader.hide();
                this.app.children.notification.showError(error.message);
                this.data.HTMLElementButton.disabled = false;
              });
          });
        }
      });
  }

  renderButton() {
    this.data.HTMLElementButton = this.data.HTMLElementBaseButton.cloneNode(
      true
    );

    this.data.HTMLElementButtonWrapper.append(this.data.HTMLElementButton);
    this.data.HTMLElementButton.disabled = !this.isSubmittable();

    this.data.conditionsComponent &&
      this.data.conditionsComponent.onChange(() => {
        this.data.HTMLElementButton.disabled = !this.isSubmittable();
      });
  }

  render() {
    this.renderButton();
    this.renderPayPalHostedFields();

    return this;
  }
}
