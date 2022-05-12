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

import { PayLaterMessagePs1_6Component } from './pay-later-message-ps1_6.component';
import { PayLaterMessagePs1_7Component } from './pay-later-message-ps1_7.component';

export class PayLaterMessageComponent extends BaseComponent {
  static Inject = {
    prestashopService: 'PrestashopService',
    querySelectorService: 'QuerySelectorService',
    config: 'PsCheckoutConfig',
    payPalService: 'PayPalService',
    psCheckoutApi: 'PsCheckoutApi',
    $: '$'
  };

  constructor(app, props) {
    super(app, props);

    this.instance = new {
      [PS_VERSION_1_6]: PayLaterMessagePs1_6Component,
      [PS_VERSION_1_7]: PayLaterMessagePs1_7Component
    }[this.prestashopService.getVersion()](app, props);
  }

  onRender(...args) {
    window.ps_checkout.events.dispatchEvent(
      new CustomEvent('payLaterOfferMessageOnRender', args)
    );
  }

  onClick(...args) {
    window.ps_checkout.events.dispatchEvent(
      new CustomEvent('payLaterOfferMessageOnClick', args)
    );
  }

  onApply(...args) {
    window.ps_checkout.events.dispatchEvent(
      new CustomEvent('payLaterOfferMessageOnApply', args)
    );
  }

  getContainerIdentifier(placement) {
    return `#ps_checkout-paypal-pay-later-message-${placement}`;
  }

  renderPayLaterOfferMessage() {
    let containerIdentifier = this.getContainerIdentifier(this.props.placement);
    let amount = 'product' === this.props.placement ? this.prestashopService.getProductPrice() : this.prestashopService.getCartAmount();
    let containerQuerySelector = this.querySelectorService.getPayLaterOfferMessageContainerSelector(this.props.placement);

    if (null === document.querySelector(containerQuerySelector)) {
      return;
    }

    this.instance.createContainer(containerIdentifier, containerQuerySelector);

    return this.payPalService
      .getPayLaterOfferMessage(this.props.placement, amount, {
        onRender: (...args) => this.onRender(...args),
        onClick: (...args) => this.onClick(...args),
        onApply: (...args) => this.onApply(...args)
      })
      .render(containerIdentifier);
  }

  render() {
    this.renderPayLaterOfferMessage();
    this.prestashopService.onUpdatedCart(() => {
      return this.renderPayLaterOfferMessage();
    });
    this.prestashopService.onUpdatedProduct(() => {
      return this.renderPayLaterOfferMessage();
    });
    return this;
  }
}
