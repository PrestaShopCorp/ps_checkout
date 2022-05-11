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
 * @typedef PaypalPayLaterOfferEvents
 * @type {*}
 *
 * @property {function} onRender
 * @property {function} onClick
 * @property {function} onApply
 */

import { BaseClass } from '../core/dependency-injection/base.class';

/**
 * @typedef PaypalHostedFieldsEvents
 * @type {*}
 *
 * @property {function} createOrder
 */

export class PayPalService extends BaseClass {
  static Inject = {
    config: 'PayPalSdkConfig',
    sdk: 'PayPalSDK',
    $: '$'
  };

  getOrderId() {
    return this.config.orderId;
  }

  getFundingSource() {
    return this.config.fundingSource;
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
      ...(this.config.buttonCustomization || {}),
      ...(window.ps_checkout.PayPalButtonCustomization || {})
    };

    if (fundingSource === 'paypal') {
      return style;
    } else if(fundingSource === 'paylater') {
      return { shape: style.shape, color: style.color };
    }

    return {};
  }

  /**
   * @param {*} fieldSelectors
   * @param {string} fieldSelectors.number
   * @param {string} fieldSelectors.cvv
   * @param {string} fieldSelectors.expirationDate
   * @param {PaypalHostedFieldsEvents} events
   * @returns {*}
   */
  getHostedFields(fieldSelectors, events) {
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
      ...(this.config.hostedFieldsCustomization || {}),
      ...(window.ps_checkout.hostedFieldsCustomization || {})
    };

    return this.sdk.HostedFields.render({
      styles: style,
      fields: {
        number: {
          selector: fieldSelectors.number,
          placeholder: this.$('paypal.hosted-fields.placeholder.card-number')
        },
        cvv: {
          selector: fieldSelectors.cvv,
          placeholder: this.$('paypal.hosted-fields.placeholder.cvv')
        },
        expirationDate: {
          selector: fieldSelectors.expirationDate,
          placeholder: this.$(
            'paypal.hosted-fields.placeholder.expiration-date'
          )
        }
      },
      ...events
    })
      .then(hostedFields => {
        const numberField = document.querySelector(fieldSelectors.number);
        const cvvField = document.querySelector(fieldSelectors.cvv);
        const expirationDateField = document.querySelector(
          fieldSelectors.expirationDate
        );

        const numberLabel = document.querySelector(
          `label[for="${numberField.id}"]`
        );
        const cvvLabel = document.querySelector(`label[for="${cvvField.id}"]`);
        const expirationDateLabel = document.querySelector(
          `label[for="${expirationDateField.id}"]`
        );

        numberLabel.innerHTML = this.$(
          'paypal.hosted-fields.label.card-number'
        );
        cvvLabel.innerHTML = this.$('paypal.hosted-fields.label.cvv');
        expirationDateLabel.innerHTML = this.$(
          'paypal.hosted-fields.label.expiration-date'
        );

        return hostedFields;
      })
      .then(hostedFields => {
        hostedFields.on('focus', event => {
          window.ps_checkout.events.dispatchEvent(
            new CustomEvent('hostedFieldsFocus', {
              detail: { ps_checkout: window.ps_checkout, event: event }
            })
          );
        });
        hostedFields.on('blur', event => {
          window.ps_checkout.events.dispatchEvent(
            new CustomEvent('hostedFieldsBlur', {
              detail: { ps_checkout: window.ps_checkout, event: event }
            })
          );
        });
        hostedFields.on('empty', event => {
          window.ps_checkout.events.dispatchEvent(
            new CustomEvent('hostedFieldsEmpty', {
              detail: { ps_checkout: window.ps_checkout, event: event }
            })
          );
        });
        hostedFields.on('notEmpty', event => {
          window.ps_checkout.events.dispatchEvent(
            new CustomEvent('hostedFieldsNotEmpty', {
              detail: { ps_checkout: window.ps_checkout, event: event }
            })
          );
        });
        hostedFields.on('validityChange', event => {
          window.ps_checkout.events.dispatchEvent(
            new CustomEvent('hostedFieldsValidityChange', {
              detail: { ps_checkout: window.ps_checkout, event: event }
            })
          );
        });
        hostedFields.on('inputSubmitRequest', () => {
          window.ps_checkout.events.dispatchEvent(
            new CustomEvent('hostedFieldsInputSubmitRequest', {
              detail: { ps_checkout: window.ps_checkout }
            })
          );
        });
        hostedFields.on('cardTypeChange', event => {
          window.ps_checkout.events.dispatchEvent(
            new CustomEvent('hostedFieldsCardTypeChange', {
              detail: { ps_checkout: window.ps_checkout, event: event }
            })
          );

          // Change card bg depending on card type
          if (event.cards.length === 1) {
            document.querySelector('.defautl-credit-card').style.display =
              'none';

            const cardImage = document.getElementById('card-image');
            cardImage.className = '';
            cardImage.classList.add(event.cards[0].type);

            document.querySelector('header').classList.add('header-slide');

            // Change the CVV length for AmericanExpress cards
            if (event.cards[0].code.size === 4) {
              hostedFields.setAttribute({
                field: 'cvv',
                attribute: 'placeholder',
                value: 'XXXX'
              });
            }
          } else {
            document.querySelector('.defautl-credit-card').style.display =
              'block';
            const cardImage = document.getElementById('card-image');
            cardImage.className = '';

            hostedFields.setAttribute({
              field: 'cvv',
              attribute: 'placeholder',
              value: 'XXX'
            });
          }
        });

        return hostedFields;
      });
  }

  getEligibleFundingSources(cache = false) {
    if (!this.eligibleFundingSources || cache) {
      const paypalFundingSources = this.sdk.getFundingSources();
      this.eligibleFundingSources = (
        this.config.fundingSourcesSorted || paypalFundingSources
      )
        .filter(
          fundingSource => paypalFundingSources.indexOf(fundingSource) >= 0
        )
        .map(fundingSource => ({
          name: fundingSource,
          mark: this.sdk.Marks({ fundingSource })
        }))
        .filter(({ name, mark }) => {
          if (name === 'card') {
            if (this.config.hostedFieldsEnabled) {
              return this.isHostedFieldsEligible()
                ? true
                : (console.error('Hosted Fields eligibility is declined'),
                  false);
            }
          }
          //TODO: REMOVE AFTER TESTING
          console.log(name, mark.isEligible());

          return mark.isEligible();
        });
    }

    return this.eligibleFundingSources;
  }

  isHostedFieldsEligible() {
    console.log(this.sdk.HostedFields && this.sdk.HostedFields.isEligible());
    return this.sdk.HostedFields && this.sdk.HostedFields.isEligible();
  }

  /**
   * @param {string} placement
   * @param {string} amount
   * @param {PaypalPayLaterOfferEvents} events
   */
  getPayLaterOfferMessage(placement, amount, events) {
    console.log('getPayLaterOfferMessage placement = ' + placement);
    const style = {
      ...{
        layout: 'text',
        logo: {
          type: 'inline'
        }
      },
      ...(this.config.payLaterOfferMessageCustomization || {}),
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
    console.log('getPayLaterOfferBanner placement = ' + placement);
    const style = {
      ...{
        layout: 'flex',
        ratio: '20x1'
      },
      ...(this.config.payLaterOfferBannerCustomization || {}),
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
}
