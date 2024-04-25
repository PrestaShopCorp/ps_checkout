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
 * @typedef PaypalButtonEvents
 * @type {*}
 *
 * @property {function} onInit
 * @property {function} onClick
 * @property {function} onError
 * @property {function} onApprove
 * @property {function} onCancel
 * @property {function} createOrder
 */

/**
 * @typedef PaypalPayLaterOfferStyle
 * @type {*}
 *
 * @property {string} layout
 * @property {string} color
 * @property {string} ratio
 * @property {object} logo
 * @property {string} logo.type
 * @property {string} logo.position
 * @property {object} text
 * @property {string} text.color
 * @property {string} text.size
 * @property {string} text.align
 */

/**
 * @typedef PayPayCardFieldsOptions
 * @type {*}
 *
 * @property {function} createOrder
 * @property {function} onApprove
 * @property {function} onError
 * @property {function} inputEvents
 * @property {object} style
 */

/**
 * @typedef PaypalPayLaterOfferEvents
 * @type {*}
 *
 * @property {function} onRender
 * @property {function} onClick
 * @property {function} onApply
 */

/**
 * @typedef PaypalMarks
 * @type {*}
 *
 * @property {function} isEligible
 * @property {function} render
 */

import { BaseClass } from '../core/dependency-injection/base.class';

export class PayPalService extends BaseClass {
  static Inject = {
    configPayPal: 'PayPalSdkConfig',
    configPrestaShop: 'PsCheckoutConfig',
    sdk: 'PayPalSDK',
    $: '$'
  };

  getOrderId() {
    return this.configPrestaShop.orderId;
  }

  getFundingSource() {
    return this.configPrestaShop.fundingSource;
  }

  /**
   * @param {string} fundingSource
   * @param {PaypalButtonEvents} events
   */
  getButtonExpress(fundingSource, events) {
    return this.sdk.Buttons({
      fundingSource: fundingSource,
      style: this.getButtonCustomizationStyle(fundingSource),
      commit: false,
      ...events
    });
  }

  /**
   * @param {string} fundingSource
   * @param {PaypalButtonEvents} events
   */
  getButtonPayment(fundingSource, events) {
    return this.sdk.Buttons({
      fundingSource: fundingSource,
      style: this.getButtonCustomizationStyle(fundingSource),
      ...events
    });
  }

  /**
   * @param {string} fundingSource
   */
  getButtonCustomizationStyle(fundingSource) {
    const style = {
      ...{ label: 'pay', color: 'gold', shape: 'pill' },
      ...(this.configPayPal.buttonCustomization || {}),
      ...(window.ps_checkout.PayPalButtonCustomization || {})
    };

    if (fundingSource === 'paypal') {
      return style;
    } else if (fundingSource === 'paylater') {
      return { shape: style.shape, color: style.color };
    }

    return {};
  }

  /**
   * @param {*} fieldSelectors
   * @param {string} fieldSelectors.name
   * @param {string} fieldSelectors.number
   * @param {string} fieldSelectors.cvv
   * @param {string} fieldSelectors.expiry
   *
   * @param {PayPayCardFieldsOptions} options
   *
   * @returns {PayPalSdk.CardFields}
   */
  async getCardFields(fieldSelectors, options) {
    const cardFields = this.sdk.CardFields(options);

    const nameField = cardFields.NameField({
      placeholder: this.$('paypal.hosted-fields.placeholder.card-name')
    });
    const numberField = cardFields.NumberField({
      placeholder: this.$('paypal.hosted-fields.placeholder.card-number')
    });
    const expiryField = cardFields.ExpiryField({
      placeholder: this.$('paypal.hosted-fields.placeholder.expiration-date')
    });
    const cvvField = cardFields.CVVField({
      placeholder: this.$('paypal.hosted-fields.placeholder.cvv')
    });

    try {
      await numberField.render(fieldSelectors.number);
      await expiryField.render(fieldSelectors.expiry);
      await cvvField.render(fieldSelectors.cvv);
      await nameField.render(fieldSelectors.name);
    } catch (e) {
      return console.error('Failed to render CardFields', e);
    }

    const nameLabel = document.querySelector(
      `label[for="${fieldSelectors.name.id}"]`
    );
    const numberLabel = document.querySelector(
      `label[for="${fieldSelectors.number.id}"]`
    );
    const cvvLabel = document.querySelector(
      `label[for="${fieldSelectors.cvv.id}"]`
    );
    const expirationDateLabel = document.querySelector(
      `label[for="${fieldSelectors.expiry.id}"]`
    );

    nameLabel.innerHTML = this.$('paypal.hosted-fields.label.card-name');
    numberLabel.innerHTML = this.$('paypal.hosted-fields.label.card-number');
    cvvLabel.innerHTML = this.$('paypal.hosted-fields.label.cvv');
    expirationDateLabel.innerHTML = this.$(
      'paypal.hosted-fields.label.expiration-date'
    );

    return cardFields;
  }

  getEligibleFundingSources(cache = false) {
    if (!this.eligibleFundingSources || cache) {
      const paypalFundingSources = this.sdk.getFundingSources();
      this.eligibleFundingSources = (
        this.configPrestaShop.fundingSourcesSorted || paypalFundingSources
      )
        .filter(
          (fundingSource) => paypalFundingSources.includes(fundingSource) || fundingSource.includes('token')
        )
        .map((fundingSource) => ({
          name: fundingSource,
          mark: fundingSource.includes('token') ? null : this.sdk.Marks({ fundingSource })
        }))
        .filter((fundingSource) => {
          if (
            fundingSource.name === 'card' &&
            this.isCardFieldsEnabled() &&
            !this.isCardFieldsEligible()
          ) {
            console.warn(
              'Card Fields (CCF) eligibility is declined. Switching to PayPal branded card fields (SCF)'
            );
          }

          if (fundingSource.name.includes('token')) {
            return true;
          }

          console.log(fundingSource.name, fundingSource.mark.isEligible());

          return fundingSource.mark.isEligible();
        });
    }

    return this.eligibleFundingSources;
  }

  isFundingEligible(fundingSource) {
    return this.getEligibleFundingSources().contains(fundingSource);
  }

  isCardFieldsEnabled() {
    return this.sdk.CardFields && this.configPrestaShop.hostedFieldsEnabled;
  }

  isCardFieldsEligible() {
    return this.sdk.CardFields && this.sdk.CardFields().isEligible();
  }

  /**
   * @param {string} placement
   * @param {string} amount
   * @param {PaypalPayLaterOfferEvents} events
   */
  getPayLaterOfferMessage(placement, amount, events) {
    const style = {
      ...{
        layout: 'text',
        logo: {
          type: 'inline'
        }
      },
      ...(this.configPayPal.payLaterOfferMessageCustomization || {}),
      ...(window.ps_checkout.payLaterOfferMessageCustomization || {})
    };
    return (
      this.sdk.Messages &&
      this.sdk.Messages({
        placement: placement,
        amount: amount,
        style: style,
        ...events
      })
    );
  }

  /**
   * @param {string} placement
   * @param {string} amount
   * @param {PaypalPayLaterOfferEvents} events
   */
  getPayLaterOfferBanner(placement, amount, events) {
    const style = {
      ...{
        layout: 'flex',
        ratio: '20x1'
      },
      ...(this.configPayPal.payLaterOfferBannerCustomization || {}),
      ...(window.ps_checkout.payLaterOfferBannerCustomization || {})
    };
    return (
      this.sdk.Messages &&
      this.sdk.Messages({
        placement: placement,
        amount: amount,
        style: style,
        ...events
      })
    );
  }

  /**
   * @param {string} fundingSource
   * @param {object} fields
   */
  getPaymentFields(fundingSource, fields = {}) {
    return this.sdk.PaymentFields && this.sdk.PaymentFields({
      fundingSource: fundingSource,
      style: this.getPaymentFieldsCustomizationStyle(fundingSource),
      fields: fields
    });
  }

  /**
   * @returns {object}
   */
  getPaymentFieldsCustomizationStyle() {
    return {
      ...(this.configPayPal.paymentFieldsCustomization || {}),
      ...(window.ps_checkout.paymentFieldsCustomization || {})
    };
  }

  /**
   * @returns {PaypalMarks}
   */
  getMarks() {
    return this.sdk.Marks && this.sdk.Marks();
  }
}
