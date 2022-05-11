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

export class PayLaterMessageComponent extends BaseComponent {
  static Inject = {
    config: 'PsCheckoutconfig',
    payPalService: 'PayPalService',
    psCheckoutApi: 'PsCheckoutApi',
    $: '$'
  };

  onRender(...args) {
    console.log('payLaterOfferMessageOnRender', args);
    window.ps_checkout.events.dispatchEvent(
      new CustomEvent('payLaterOfferMessageOnRender')
    );
  }

  onClick(...args) {
    console.log('payLaterOfferMessageOnClick', args);
    window.ps_checkout.events.dispatchEvent(
      new CustomEvent('payLaterOfferMessageOnClick')
    );
  }

  onApply(...args) {
    console.log('payLaterOfferMessageOnApply', args);
    window.ps_checkout.events.dispatchEvent(
      new CustomEvent('payLaterOfferMessageOnApply')
    );
  }

  renderPayLaterOfferMessage() {
    return this.payPalService
      .getPayLaterOfferMessage(this.props.placement, this.props.amount, {
        onRender: (...args) => this.onRender(...args),
        onClick: (...args) => this.onClick(...args),
        onApply: (...args) => this.onApply(...args)
      })
      .render(this.props.querySelector);
  }

  render() {
    console.log('renderPayLaterOfferMessage');
    this.renderPayLaterOfferMessage();
    return this;
  }
}
