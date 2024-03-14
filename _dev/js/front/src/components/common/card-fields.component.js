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
    querySelectorService: 'QuerySelectorService',
    $: '$'
  };

  created() {
    this.data.name = this.props.fundingSource.name;
    this.data.validity = false;
    this.data.orderId = null;
    /**
     * @property {PaypalCardFieldsEvent} data.cardFieldsState
     */
    this.data.cardFieldsState = {};

    this.data.cardFieldsFocused = {
      name: false,
      number: false,
      expiry: false,
      cvv: false
    };
    this.data.HTMLElement = this.props.HTMLElement;
    this.data.HTMLElementCardForm =
      this.querySelectorService.getCardFieldsFormContainer();
    this.data.HTMLElementBaseButton =
      this.querySelectorService.getBasePaymentConfirmation();
    this.data.HTMLElementButton = null;
    this.data.HTMLElementButtonWrapper = this.getButtonWrapper();

    this.data.HTMLElementCardHolderName =
      this.querySelectorService.getCardFieldsNameInputContainer();
    this.data.HTMLElementCardNumber =
      this.querySelectorService.getCardFieldsNumberInputContainer();
    this.data.HTMLElementCardExpiry =
      this.querySelectorService.getCardFieldsExpiryInputContainer();
    this.data.HTMLElementCardCvv =
      this.querySelectorService.getCardFieldsCvvInputContainer();

    this.data.HTMLElementCardNameError =
      this.querySelectorService.getCardFieldsNameError();
    this.data.HTMLElementCardNumberError =
      this.querySelectorService.getCardFieldsNumberError();
    this.data.HTMLElementCardVendorError =
      this.querySelectorService.getCardFieldsVendorError();
    this.data.HTMLElementCardExpiryError =
      this.querySelectorService.getCardFieldsExpiryError();
    this.data.HTMLElementCardCvvError =
      this.querySelectorService.getCardFieldsCvvError();
  }

  getButtonWrapper() {
    const buttonWrapper = `.ps_checkout-button[data-funding-source=${this.data.name}]`;
    return document.querySelector(buttonWrapper);
  }

  isSubmittable() {
    return this.data.conditions
      ? this.data.conditions.isChecked() && this.data.validity
      : this.data.validity;
  }

  isFormValid() {
    const { cardNameField, cardNumberField, cardExpiryField, cardCvvField } =
      this.data.cardFieldsState.fields;
    return (
      (cardNameField.isEmpty || cardNameField.isValid) &&
      cardNumberField.isValid &&
      cardExpiryField.isValid &&
      cardCvvField.isValid
    );
  }

  setFocusedField(fieldName) {
    this.data.cardFieldsFocused[fieldName] = true;
  }

  toggleCardNameFieldError() {
    const { isFocused, isEmpty, isValid, isPotentiallyValid } =
      this.data.cardFieldsState.fields.cardNameField;
    const hideError = isEmpty || isFocused || isValid;

    this.data.HTMLElementCardNameError.classList.toggle('hidden', hideError);
  }

  toggleCardNumberFieldError() {
    const { isFocused, isEmpty, isValid, isPotentiallyValid } =
      this.data.cardFieldsState.fields.cardNumberField;
    const hideError =
      isFocused || !this.data.cardFieldsFocused.number || isValid;

    this.data.HTMLElementCardNumberError.classList.toggle(
      'hidden',
      !isPotentiallyValid || hideError
    );
    this.data.HTMLElementCardVendorError.classList.toggle(
      'hidden',
      isPotentiallyValid
    );
  }

  toggleCardExpiryFieldError() {
    const { isFocused, isEmpty, isValid, isPotentiallyValid } =
      this.data.cardFieldsState.fields.cardExpiryField;
    const hideError =
      isPotentiallyValid &&
      (isFocused || !this.data.cardFieldsFocused.expiry || isValid);

    this.data.HTMLElementCardExpiryError.classList.toggle('hidden', hideError);
  }
  toggleCardCvvFieldError() {
    const { isFocused, isEmpty, isValid, isPotentiallyValid } =
      this.data.cardFieldsState.fields.cardCvvField;
    const hideError = isFocused || !this.data.cardFieldsFocused.cvv || isValid;

    this.data.HTMLElementCardCvvError.classList.toggle('hidden', hideError);
  }

  toggleCardFieldsErrors() {
    this.toggleCardNameFieldError();
    this.toggleCardNumberFieldError();
    this.toggleCardExpiryFieldError();
    this.toggleCardCvvFieldError();
  }

  /**
   * @param {PaypalCardFieldsEvent} event
   */
  updateCardFieldsState(event) {
    this.setFocusedField(event.emittedBy);
    this.data.cardFieldsState = event;
    this.data.validity = this.isFormValid();

    this.isSubmittable()
      ? this.data.HTMLElementButton.removeAttribute('disabled')
      : this.data.HTMLElementButton.setAttribute('disabled', '');

    this.toggleCardFieldsErrors();
  }

  getVaultFormData() {
    if (this.data.HTMLElementCardForm) {
      const formData = new FormData(this.data.HTMLElementCardForm);
      return {
        vault: formData.get(`ps_checkout-vault-payment-${this.data.name}`) === '1',
        favorite: formData.get(`ps_checkout-favorite-payment-${this.data.name}`) === '1'
      };

    }
    return {};
  }

  renderPayPalCardFields() {
    this.data.HTMLElementCardForm.classList.toggle('loading', true);

    const style = {
      ...{
        input: {
          'font-size': '17px',
          'font-family': 'helvetica, tahoma, calibri, sans-serif',
          color: '#3a3a3a',
          padding: '8px 12px'
        },
        ':focus': {
          color: 'black'
        },
        body: {
          padding: '0px'
        }
      },
      ...(this.configPayPal.hostedFieldsCustomization || {}),
      ...(window.ps_checkout.hostedFieldsCustomization || {})
    };

    this.payPalService
      .getCardFields(
        {
          name: this.data.HTMLElementCardHolderName,
          number: this.data.HTMLElementCardNumber,
          cvv: this.data.HTMLElementCardCvv,
          expiry: this.data.HTMLElementCardExpiry
        },
        {
          style,
          createOrder: async (data) => {
            this.data.HTMLElementButton.setAttribute('disabled', true);

            return this.psCheckoutApi
              .postCreateOrder({
                ...this.getVaultFormData(),
                ...data,
                fundingSource: this.data.name,
                isHostedFields: true
                // vault: storeCardInVault
              })
              .then((data) => {
                this.data.orderId = data;
                return data;
              })
              .catch((error) => {
                throw error;
              });
          },
          onApprove: async (data) => {
            return this.psCheckoutApi
              .postValidateOrder({
                ...data,
                fundingSource: this.data.name,
                isHostedFields: true
              })
              .catch((error) => {
                let message = error.message || '';

                if (!message) {
                  message = `Unknown error, code: ${
                    error.code || 'none'
                  }, description: ${error.description || 'none'}`;
                }

                this.data.loader.hide();
                this.data.notification.showError(message);
                this.data.HTMLElementButton.removeAttribute('disabled');
              });
          },
          onError: async (error) => {
            this.data.loader.hide();
            let message = error.message || this.$('checkout.form.error.label');
            this.data.notification.showError(message);
            this.data.HTMLElementButton.removeAttribute('disabled');

            return this.psCheckoutApi
              .postCancelOrder({
                orderID: this.data.orderId,
                fundingSource: this.data.name,
                isExpressCheckout: this.config.expressCheckout.active,
                reason: 'card_fields_error',
                error: error instanceof Error ? error.message : error
              })
              .catch((error) => console.error(error));
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
            }
          }
        }
      )
      .then((cardFields) => {
        this.data.HTMLElementCardForm.classList.toggle('loading', false);

        if (this.data.HTMLElementCardForm.style.display === 'none') {
          this.data.HTMLElementCardForm.style.display = 'block';
        }

        if (this.data.HTMLElement !== null) {
          this.data.HTMLElementButton.addEventListener('click', (event) => {
            event.preventDefault();

            if (!this.data.validity) {
              this.data.HTMLElementButton.setAttribute('disabled', '');
              return;
            }

            this.data.loader.show();
            this.data.HTMLElementButton.setAttribute('disabled', '');

            cardFields.submit();
          });
        }
      });
  }

  renderButton() {
    this.data.HTMLElementButton =
      this.data.HTMLElementBaseButton.cloneNode(true);

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
