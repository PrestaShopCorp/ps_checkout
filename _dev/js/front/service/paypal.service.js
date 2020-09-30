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
  constructor(sdk) {
    this.sdk = sdk;
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
          placeholder: 'Card number' //@todo translations
        },
        cvv: {
          selector: fieldSelectors.cvv,
          placeholder: 'CVV' //@todo translations
        },
        expirationDate: {
          selector: fieldSelectors.expirationDate,
          placeholder: 'MM/YYYY'
        }
      },
      ...events
    });
  }

  getEligibleFundingSources(cache) {
    if (!this.eligibleFundingSources || cache) {
      this.eligibleFundingSources = this.sdk
        .getFundingSources()
        .map(fundingSource => ({
          name: fundingSource,
          mark: this.sdk.Marks({ fundingSource })
        }))
        .filter(({ mark }) => mark.isEligible());
    }

    return this.eligibleFundingSources;
  }

  isHostedFieldsEligible() {
    return true;
    // return this.sdk.HostedFields && this.sdk.HostedFields.isEligible();
  }
}
