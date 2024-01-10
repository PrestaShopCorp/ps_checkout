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

/**
 * @typedef PaypalCardFieldCardField
 * @type {*}
 *
 * @property {boolean} isEmpty
 * @property {boolean} isValid
 * @property {boolean} isPotentiallyValid
 * @property {boolean} isFocused
 */

/**
 * @typedef PaypalCardFieldsEvent
 * @type {*}
 *
 * @property {string} emittedBy
 * @property {boolean} isFormValid
 * @property {String[]} errors
 * @property {*} fields
 * @property {PaypalCardFieldCardField} fields.cardCvvField
 * @property {PaypalCardFieldCardField} fields.cardExpiryField
 * @property {PaypalCardFieldCardField} fields.cardNameField
 * @property {PaypalCardFieldCardField} fields.cardNumberField
 */

import { BaseComponent } from '../../core/dependency-injection/base.component';

export class CardFieldsComponent extends BaseComponent {
  static Inject = {
    config: 'PsCheckoutConfig',
    configPayPal: 'PayPalSdkConfig',
    payPalService: 'PayPalService',
    psCheckoutApi: 'PsCheckoutApi',
    psCheckoutService: 'PsCheckoutService',
  };

  created() {
    this.data.name = this.props.fundingSource.name;
    this.data.validity = false;
    /**
     * @property {PaypalCardFieldsEvent} data.cardFieldsState
     */
    this.data.cardFieldsState = {};

    this.data.cardFieldsFocused = {
      name: false,
      number: false,
      expiry: false,
      cvv: false,
    }

    this.data.HTMLElement = this.props.HTMLElement;
    this.data.HTMLElementCardForm = this.getCardForm();
    this.data.HTMLElementBaseButton = this.getBaseButton();
    this.data.HTMLElementButton = null;
    this.data.HTMLElementButtonWrapper = this.getButtonWrapper();
    this.data.HTMLElementCardHolderName = this.getCardHolderName();
    this.data.HTMLElementCardNumber = this.getCardNumber();
    this.data.HTMLElementCardCVV = this.getCardCVV();
    this.data.HTMLElementCardExpirationDate = this.getCardExpirationDate();

    this.data.HTMLElementCardNameError= this.getCardNameFieldError();
    this.data.HTMLElementCardNumberError= this.getCardNumberFieldError();
    this.data.HTMLElementCardVendorError= this.getCardVendorFieldError();
    this.data.HTMLElementCardExpiryError= this.getCardExpiryFieldError();
    this.data.HTMLElementCardCvvError= this.getCardCvvFieldError();

    this.data.HTMLElementSection = this.getSection();
  }


  getCardForm() {
    const cardFromSelector = `#ps_checkout-hosted-fields-form`;
    return document.querySelector(cardFromSelector);
  }
  getBaseButton() {
    const buttonSelector = `#payment-confirmation button`;
    return document.querySelector(buttonSelector);
  }
  getButtonWrapper() {
    const buttonWrapper = `.ps_checkout-button[data-funding-source=${this.data.name}]`;
    return document.querySelector(buttonWrapper);
  }

  getCardHolderName() {
    const cardHolderNameId = '#ps_checkout-hosted-fields-card-name';
    return document.getElementById(cardHolderNameId);
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
  getCardNameFieldError() {
    const cardNameErrorSelector = `#ps_checkout-hosted-fields-error-name`;
    return document.querySelector(cardNameErrorSelector);
  }

  getCardNumberFieldError() {
    const cardNameErrorSelector = `#ps_checkout-hosted-fields-error-number`;
    return document.querySelector(cardNameErrorSelector);
  }

  getCardVendorFieldError() {
    const cardVendorErrorSelector = `#ps_checkout-hosted-fields-error-vendor`;
    return document.querySelector(cardVendorErrorSelector);
  }

  getCardExpiryFieldError() {
    const cardNameErrorSelector = `#ps_checkout-hosted-fields-error-expiry`;
    return document.querySelector(cardNameErrorSelector);
  }

  getCardCvvFieldError() {
    const cardNameErrorSelector = `#ps_checkout-hosted-fields-error-cvv`;
    return document.querySelector(cardNameErrorSelector);
  }

  getContingencies() {
    switch (this.config.hostedFieldsContingencies) {
      case '3D_SECURE':
      case 'SCA_ALWAYS':
        return ['SCA_ALWAYS'];
      case 'NONE':
        return undefined;
      default:
        return ['SCA_WHEN_REQUIRED'];
    }
  }

  isSubmittable() {
    return this.data.conditions
      ? this.data.conditions.isChecked() && this.data.validity
      : this.data.validity;
  }

  setFieldFocus(fieldName) {
    this.data.cardFieldsFocused[fieldName] = true;
  }

  toggleCardNameFieldError() {
    const { isFocused, isEmpty, isValid, isPotentiallyValid } =
      this.data.cardFieldsState.fields.cardNameField;
    const hideError = isFocused || !this.data.cardFieldsFocused.name || isValid || isPotentiallyValid;

    this.data.HTMLElementCardNameError.classList.toggle('hidden', hideError)
  }

  toggleCardNumberFieldError() {
    const { isFocused, isEmpty, isValid, isPotentiallyValid } =
      this.data.cardFieldsState.fields.cardNumberField;
    const hideError = isFocused || !this.data.cardFieldsFocused.number || isValid;

    this.data.HTMLElementCardNumberError.classList.toggle('hidden', !isPotentiallyValid || hideError)
    this.data.HTMLElementCardVendorError.classList.toggle('hidden', isPotentiallyValid)
  }

  toggleCardExpiryFieldError() {
    const { isFocused, isEmpty, isValid, isPotentiallyValid } =
      this.data.cardFieldsState.fields.cardExpiryField;
    const hideError = isPotentiallyValid && (isFocused || !this.data.cardFieldsFocused.expiry || isValid);

    this.data.HTMLElementCardExpiryError.classList.toggle('hidden', hideError)
  }
  toggleCardCvvFieldError() {
    const { isFocused, isEmpty, isValid, isPotentiallyValid } =
      this.data.cardFieldsState.fields.cardCvvField;
    const hideError = isPotentiallyValid && (isFocused || !this.data.cardFieldsFocused.cvv || isValid);

    this.data.HTMLElementCardCvvError.classList.toggle('hidden', hideError)
  }

  toggleCardFieldErrors() {
    this.toggleCardNameFieldError();
    this.toggleCardNumberFieldError();
    this.toggleCardExpiryFieldError();
    this.toggleCardCvvFieldError();
  }

  /**
   * @param {PaypalCardFieldsEvent} event
   */
  updateCardFieldsState(event) {
    this.setFieldFocus(event.emittedBy);
    this.data.validity = event.isFormValid;
    this.data.cardFieldsState = event;

    this.isSubmittable()
      ? this.data.HTMLElementButton.removeAttribute('disabled')
      : this.data.HTMLElementButton.setAttribute('disabled', '');

    this.toggleCardFieldErrors();
  }

  renderPayPalCardFields() {
    this.data.HTMLElementCardForm.classList.toggle('loading', true);

    const style = {
      ...{
        input: {
          'font-size': '17px',
          'font-family': 'helvetica, tahoma, calibri, sans-serif',
          color: '#3a3a3a'
        },
        ':focus': {
          color: 'black'
        }
      },
      ...(this.configPayPal.hostedFieldsCustomization || {}),
      ...(window.ps_checkout.hostedFieldsCustomization || {})
    };

    this.payPalService
      .getCardFields(
        {
          name: '#ps_checkout-hosted-fields-card-name',
          number: '#ps_checkout-hosted-fields-card-number',
          cvv: '#ps_checkout-hosted-fields-card-cvv',
          expirationDate: '#ps_checkout-hosted-fields-card-expiration-date'
        },
        {
          style,
          createOrder: async (data) => {
            this.data.HTMLElementButton.setAttribute('disabled', true);

            return this.psCheckoutApi
              .postCreateOrder({
                ...data,
                fundingSource: this.data.name,
                isCardFields: true,
                // vault: storeCardInVault
              })
              .then(data => {
                return data;
              })
              .catch(error => {
                this.data.notification.showError(
                  `${error.message} ${error.name}`
                );
              })
          },
          onApprove: async (data) => {
            return this.psCheckoutApi.postValidateOrder({
              ...data,
              fundingSource: this.data.name,
              isHostedFields: true
            })
            .catch(error => {
              let message = error.message || '';

              if (!message) {
                message = `Unknown error, code: ${error.code || 'none'}, description: ${error.description || 'none'}`;
              }

              this.data.loader.hide();
              this.data.notification.showError(message);
              this.data.HTMLElementButton.removeAttribute('disabled');
            })
          },
          onError: async (error) => {
            this.data.loader.hide();
            let message = error.message || '';
            this.data.notification.showError(message);
            this.data.HTMLElementButton.removeAttribute('disabled');
          },
          inputEvents: {
            /**
             * @param {PaypalCardFieldsEvent} event
             */
            onChange: (event) => {
              this.updateCardFieldsState(event);
              this.data.cardFields = event;
            },
            /**
             * @param {PaypalCardFieldsEvent} event
             */
            onFocus: (event) => {
              this.updateCardFieldsState(event);
              window.ps_checkout.events.dispatchEvent(
                new CustomEvent('hostedFieldsFocus', {
                  detail: { ps_checkout: window.ps_checkout, event }
                })
              );
            },
            /**
             * @param {PaypalCardFieldsEvent} event
             */
            onBlur: (event) => {
              this.updateCardFieldsState(event);
              window.ps_checkout.events.dispatchEvent(
                new CustomEvent('hostedFieldsBlur', {
                  detail: { ps_checkout: window.ps_checkout, event }
                })
              );
            },
            /**
             * @param {PaypalCardFieldsEvent} event
             */
            onInputSubmitRequest: (event) => {
              this.updateCardFieldsState(event);
            },
          }

        },
      )
      .then(cardFields => {
        this.data.HTMLElementCardForm.classList.toggle('loading', false);
        if (this.data.HTMLElement !== null) {
          this.data.HTMLElementButton.addEventListener('click', event => {
            event.preventDefault();
            this.data.loader.show();
            // this.data.HTMLElementButton.classList.toggle('disabled', true);
            this.data.HTMLElementButton.setAttribute('disabled', '');

            cardFields.submit({contingencies: this.getContingencies()});
          });
        }
      });
  }



  renderButton() {
    this.data.HTMLElementButton = this.data.HTMLElementBaseButton.cloneNode(
      true
    );

    this.data.HTMLElementButtonWrapper.append(this.data.HTMLElementButton);
    this.data.HTMLElementButton.classList.remove('disabled');
    this.data.HTMLElementButton.style.display = '';
    this.data.HTMLElementButton.disabled = !this.isSubmittable();

    this.data.conditions &&
    this.data.conditions.onChange(() => {
      // In some PS versions, the handler fails to disable the button because of the timing.
      setTimeout(() => {
        this.data.HTMLElementButton.disabled = !this.isSubmittable();
      }, 0);
    });
  }

  render() {
    this.data.conditions = this.app.root.children.conditionsCheckbox;
    this.data.notification = this.app.root.children.notification;
    this.data.loader = this.app.root.children.loader;

    this.renderButton();
    this.renderPayPalCardFields();

    return this;
  }
}
