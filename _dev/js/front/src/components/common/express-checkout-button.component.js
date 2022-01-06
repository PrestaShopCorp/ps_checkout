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
import { NotificationComponent } from './notification.component';

export class ExpressCheckoutButtonComponent extends BaseComponent {
  static Inject = {
    payPalService: 'PayPalService',
    psCheckoutApi: 'PsCheckoutApi',
    $: '$'
  };

  onInit(data, actions) {
    return actions.enable();
  }

  onClick(data, actions) {
    return (
      this.psCheckoutApi
        .postCheckCartOrder(
          { ...data, fundingSource: 'paypal', isExpressCheckout: true },
          actions
        )
        // TODO: Error notification
        .catch(() => actions.reject())
    );
    // TODO: [PAYSHIP-605] Error handling
  }

  onError(error) {
    console.log('PRESTAAAAA', this.app, this.props.querySelector);
    // this.data.notification.showError(error.message);
    // prestashop.emit('showErrorNextToAddtoCartButton', { errorMessage: error.message});
    this.notification.showError(error.message);
    return console.error(error);
  }

  onApprove(data, actions) {
    return this.psCheckoutApi.postExpressCheckoutOrder(
      {
        ...data,
        fundingSource: 'paypal',
        isExpressCheckout: true
      },
      actions
    );
  }

  onCancel(data) {
    return this.psCheckoutApi.postCancelOrder({
      ...data,
      fundingSource: 'paypal',
      isExpressCheckout: true
    });
  }

  createOrder(data) {
    if (this.props.createOrder) {
      return this.props.createOrder(data);
    }
  }

  renderPayPalButton() {
    if (
      !this.payPalService
        .getEligibleFundingSources()
        .filter(({ name }) => name === 'paypal').length > 0
    )
      return;

    return this.payPalService
      .getButtonExpress('paypal', {
        onInit: (data, actions) => this.onInit(data, actions),
        onClick: (data, actions) => this.onClick(data, actions),
        onError: (error) => this.onError(error),
        onApprove: (data, actions) => this.onApprove(data, actions),
        onCancel: (data) => this.onCancel(data),
        createOrder: (data) => this.createOrder(data)
      })
      .render(this.props.querySelector);
  }
  created() {
    this.app.root = this;
  }

  render() {
    this.notification = new NotificationComponent(this.app).render();
    this.renderPayPalButton();
    return this;
  }
}
