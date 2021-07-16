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

  /**
   * @param {string} fundingSource
   * @param {PaypalButtonEvents} events
   */
  getButtonExpress(fundingSource, events) {
    const style = {
      ...{ label: 'checkout' },
      ...(this.config.expressCheckoutButtonCustomization || {}),
      ...(window.ps_checkout.PayPalExpressCheckoutButtonCustomization || {})
    };
    return this.sdk.Buttons({
      fundingSource: fundingSource,
      style: fundingSource === 'paypal' ? style : { shape: style.shape },
      commit: false,
      ...events
    });
  }

  /**
   * @param {string} fundingSource
   * @param {PaypalButtonEvents} events
   */
  getButtonPayment(fundingSource, events) {
    const style = {
      ...{ label: 'pay' },
      ...(this.config.buttonCustomization || {}),
      ...(window.ps_checkout.PayPalButtonCustomization || {})
    };
    return this.sdk.Buttons({
      fundingSource: fundingSource,
      style: fundingSource === 'paypal' ? style : { shape: style.shape },
      ...events
    });
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
    return this.sdk.HostedFields.render({
      styles: {
        input: {
          'font-size': '17px',
          'font-family': 'helvetica, tahoma, calibri, sans-serif',
          color: '#3a3a3a'
        },
        ':focus': {
          color: 'black'
        }
      },
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
        hostedFields.on('cardTypeChange', ({ cards }) => {
          // Change card bg depending on card type
          if (cards.length === 1) {
            document.querySelector('.defautl-credit-card').style.display =
              'none';

            const cardImage = document.getElementById('card-image');
            cardImage.className = '';
            cardImage.classList.add(cards[0].type);

            document.querySelector('header').classList.add('header-slide');

            // Change the CVV length for AmericanExpress cards
            if (cards[0].code.size === 4) {
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

          return mark.isEligible();
        });
    }

    return this.eligibleFundingSources;
  }

  isHostedFieldsEligible() {
    return this.sdk.HostedFields && this.sdk.HostedFields.isEligible();
  }
}
