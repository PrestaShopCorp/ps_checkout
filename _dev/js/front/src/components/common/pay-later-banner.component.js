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

export class PayLaterBannerComponent extends BaseComponent {
  static Inject = {
    config: 'PsCheckoutconfig',
    payPalService: 'PayPalService',
    psCheckoutApi: 'PsCheckoutApi',
    $: '$'
  };

  onRender(...args) {
    console.log('payLaterOfferBannerOnRender', args);
    window.ps_checkout.events.dispatchEvent(
      new CustomEvent('payLaterOfferBannerOnRender')
    );
  }

  onClick(...args) {
    console.log('payLaterOfferBannerOnClick', args);
    window.ps_checkout.events.dispatchEvent(
      new CustomEvent('payLaterOfferBannerOnClick')
    );
  }

  onApply(...args) {
    console.log('payLaterOfferBannerOnApply', args);
    window.ps_checkout.events.dispatchEvent(
      new CustomEvent('payLaterOfferBannerOnApply')
    );
  }

  renderPayLaterOfferBanner() {
    return this.payPalService
      .getPayLaterOfferBanner(this.props.placement, this.props.amount, {
        onRender: (...args) => this.onRender(...args),
        onClick: (...args) => this.onClick(...args),
        onApply: (...args) => this.onApply(...args)
      })
      .render(this.props.querySelector);
  }

  render() {
    console.log('renderpayLaterOfferBanner');
    this.renderPayLaterOfferBanner();
    return this;
  }
}
