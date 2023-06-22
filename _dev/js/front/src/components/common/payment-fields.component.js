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
import { BaseComponent } from '../../core/dependency-injection/base.component';

/**
 * @typedef PaymentFieldsComponentProps
 *
 * @param {string} fundingSource.name
 * @param {*}      fundingSource.mark
 *
 * @param {HTMLElement} HTMLElement
 */

export class PaymentFieldsComponent extends BaseComponent {
  static Inject = {
    config: 'PsCheckoutConfig',
    payPalService: 'PayPalService'
  };

  created() {
    this.data.name = this.props.fundingSource.name;

    this.data.HTMLElement = this.props.HTMLElement;
  }

  render() {
    const containerSelector = `.ps_checkout-payment-fields[data-funding-source=${this.data.name}]`;

    this.data.HTMLElement.classList.add('ps_checkout-payment-fields');
    this.data.HTMLElement.setAttribute('data-funding-source', this.data.name);

    const paymentFields = this.payPalService
      .getPaymentFields(this.data.name);

    if (paymentFields) {
      paymentFields.render(containerSelector);
    }

    return this;
  }
}
