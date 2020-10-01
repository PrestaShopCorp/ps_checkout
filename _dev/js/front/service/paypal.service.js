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
 * @typedef PaypalHostedFieldsEvents
 * @type {*}
 *
 * @property {function} createOrder
 */

export class PaypalService {
  constructor(sdk, config, translationService) {
    this.sdk = sdk;
    this.config = config;
    this.translationService = translationService;

    this.$ = id => this.translationService.getTranslationString(id);
  }

  /**
   * @param {string} fundingSource
   * @param {PaypalButtonEvents} events
   */
  getButtonExpress(fundingSource, events) {
    return this.sdk.Buttons({
      fundingSource: fundingSource,
      style: {
        label: 'pay',
        commit: false
      },
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
      style: {
        label: 'pay'
      },
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
          placeholder: this.$('paypal.hosted-fields.card-number')
        },
        cvv: {
          selector: fieldSelectors.cvv,
          placeholder: this.$('paypal.hosted-fields.cvv')
        },
        expirationDate: {
          selector: fieldSelectors.expirationDate,
          placeholder: this.$('paypal.hosted-fields.expiration-date')
        }
      },
      ...events
    });
  }

  getEligibleFundingSources(cache) {
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
        .filter(({ mark }) => mark.isEligible());
    }

    return this.eligibleFundingSources;
  }

  isHostedFieldsEligible() {
    return this.sdk.HostedFields && this.sdk.HostedFields.isEligible();
  }
}
