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

export class PaymentMethodLogosComponent extends BaseComponent {
  static Inject = {
    config: 'PsCheckoutConfig',
    payPalService: 'PayPalService',
    querySelectorService: 'QuerySelectorService',
    prestashopService: 'PrestashopService',
    $: '$'
  };

  created() {
    this.data.HTMLElement = this.props.HTMLElement;
  }

  render() {
    this.renderMark();

    this.prestashopService.onUpdatedCart(() => {
      return this.renderMark();
    });

    this.prestashopService.onUpdatedProduct(() => {
      return this.renderMark();
    });

    return this;
  }

  renderMark() {
    let containerLogoIdentifier = `#ps_checkout-payment-method-logos-container`;
    const containerLogoQuerySelector = this.querySelectorService.getPaymentMethodLogoContainer(this.props.placement);
    const fundingSources = this.payPalService.getEligibleFundingSources();

    if (containerLogoQuerySelector) {
      const containerLogo = document.querySelector(containerLogoIdentifier);

      if (null === containerLogo) {
        let containerLogoElement = this.createContainer(containerLogoIdentifier, containerLogoQuerySelector);

        fundingSources.forEach(fundingSource => {
          if (this.hasCustomMark(fundingSource.name)) {
            this.renderCustomMark(fundingSource.name, containerLogoElement);
          } else {
            fundingSource.mark.render(containerLogoElement);
          }
        });
      }
    }
  }

  createContainer(containerIdentifier, querySelector) {
    const container = document.querySelector(containerIdentifier);

    if (null === container) {
      let containerParentElement = document.createElement('div');
      containerParentElement.id = 'ps_checkout-payment-method-logo-block-container';

      let titleImg = document.createElement('img');
      titleImg.id = 'ps_checkout-payment-method-logo-block-img';
      titleImg.setAttribute('alt', this.$('payment-method-logos.title'));
      titleImg.setAttribute('src', this.config.imgTitlePaymentMethodLogos);

      let title = document.createElement('div');
      title.id = 'ps_checkout-payment-method-logo-block-title';
      title.innerText = this.$('payment-method-logos.title');
      title.prepend(titleImg);
      containerParentElement.append(title);

      let containerLogoElement = document.createElement('div');
      containerLogoElement.id = containerIdentifier.slice(1);

      containerParentElement.append(containerLogoElement);

      querySelector.append(containerParentElement);

      return containerLogoElement;
    }

    return container;
  }

  hasCustomMark(fundingSource) {
    return this.config.customMark[fundingSource];
  }

  renderCustomMark(fundingSource, containerQuerySelector) {
    const src = this.config.customMark[fundingSource];

    let containerElement = document.createElement('div');
    containerElement.classList.add('paypal-mark');

    let customMarkImg = document.createElement('img');
    customMarkImg.classList.add('cards-logo');
    customMarkImg.setAttribute('alt', fundingSource);
    customMarkImg.setAttribute('src', src);
    containerElement.append(customMarkImg);

    containerQuerySelector.append(containerElement);
  }
}
