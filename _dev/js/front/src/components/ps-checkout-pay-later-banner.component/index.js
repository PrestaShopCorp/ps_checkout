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
import {
  PS_VERSION_1_6,
  PS_VERSION_1_7
} from '../../constants/ps-version.constants';

import { PayLaterBannerPs1_6Component } from './pay-later-banner-ps1_6.component';
import { PayLaterBannerPs1_7Component } from './pay-later-banner-ps1_7.component';

export class PayLaterBannerComponent extends BaseComponent {

  static Inject = {
    prestashopService: 'PrestashopService',
    config: 'PsCheckoutConfig',
    payPalService: 'PayPalService',
    psCheckoutApi: 'PsCheckoutApi',
    $: '$'
  };

  constructor(app, props) {
    super(app, props);

    this.instance = new {
      [PS_VERSION_1_6]: PayLaterBannerPs1_6Component,
      [PS_VERSION_1_7]: PayLaterBannerPs1_7Component
    }[this.prestashopService.getVersion()](app, props);
  }

  onRender(...args) {
    window.ps_checkout.events.dispatchEvent(
      new CustomEvent('payLaterOfferBannerOnRender', args)
    );
  }

  onClick(...args) {
    window.ps_checkout.events.dispatchEvent(
      new CustomEvent('payLaterOfferBannerOnClick', args)
    );
  }

  onApply(...args) {
    window.ps_checkout.events.dispatchEvent(
      new CustomEvent('payLaterOfferBannerOnApply', args)
    );
  }

  getContainerIdentifier(placement) {
    return `#ps_checkout-paypal-pay-later-banner-${placement}`;
  }

  renderPayLaterOfferBanner() {
    let containerIdentifier = this.getContainerIdentifier(this.props.placement);

    this.instance.createContainer(containerIdentifier, this.props.querySelector);

    return this.payPalService
      .getPayLaterOfferBanner(this.props.placement, this.props.amount, {
        onRender: (...args) => this.onRender(...args),
        onClick: (...args) => this.onClick(...args),
        onApply: (...args) => this.onApply(...args)
      })
      .render(containerIdentifier);
  }

  render() {
    this.renderPayLaterOfferBanner();
    this.prestashopService.onUpdatedCart(() => {
      return this.renderPayLaterOfferBanner();
    });
    this.prestashopService.onUpdatedProduct(() => {
      return this.renderPayLaterOfferBanner();
    });
    return this;
  }
}
