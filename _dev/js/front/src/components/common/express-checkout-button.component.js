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

export class ExpressCheckoutButtonComponent extends BaseComponent {
  static Inject = {
    payPalService: 'PayPalService',
    psCheckoutApi: 'PsCheckoutApi',
    $: '$'
  };

  created() {
    this.data.orderId = this.payPalService.getOrderId();
  }

  onInit(data, actions) {
    return actions.enable();
  }

  onClick(data, actions) {
    return this.psCheckoutApi
      .postCheckCartOrder(
        {
          ...data,
          fundingSource: this.props.fundingSource,
          isExpressCheckout: true,
          orderID: this.data.orderId
        },
        actions
      )
      .catch((error) => {
        actions.reject();
        throw error;
      });
  }

  onError(error) {
    const errorText = error?.message ? error.message : error;
    this.notifyError(errorText);
    console.error(error);

    return this.psCheckoutApi
      .postCancelOrder({
        orderID: this.data.orderId,
        fundingSource: this.props.fundingSource,
        isExpressCheckout: true,
        reason: 'express_checkout_error',
        error: errorText
      })
      .catch((error) => console.error(error));
  }

  onApprove(data, actions) {
    return this.psCheckoutApi.postExpressCheckoutOrder(
      {
        ...data,
        fundingSource: this.props.fundingSource,
        isExpressCheckout: true
      },
      actions
    );
  }

  onCancel(data) {
    return this.psCheckoutApi.postCancelOrder({
      ...data,
      orderID: this.data.orderId,
      fundingSource: this.props.fundingSource,
      isExpressCheckout: true,
      reason: 'express_checkout_cancelled'
    });
  }

  createOrder(data) {
    const extraData = this.props?.data ? this.props.data : {};

    return this.psCheckoutApi
      .postCreateOrder({
        ...data,
        ...extraData
      })
      .then((data) => {
        this.data.orderId = data;
        return data;
      })
      .catch((error) => {
        throw error;
      });
  }

  notifyError(message) {
    const expressCheckoutContainer = document.querySelector(
      this.props.querySelector
    );
    const notificationContainerIdentifier =
      'ps_checkout-product-notification-container';
    let notificationContainerElement = document.getElementById(
      notificationContainerIdentifier
    );

    if (!notificationContainerElement) {
      notificationContainerElement = document.createElement('div');
      notificationContainerElement.id = notificationContainerIdentifier;
      expressCheckoutContainer.prepend(notificationContainerElement);
    }

    const notificationIdentifier = 'ps_checkout-product-notification-container';
    const currentNotificationElement = document.querySelector(
      '#' + notificationContainerIdentifier + ' .' + notificationIdentifier
    );

    if (currentNotificationElement) {
      return (currentNotificationElement.textContent = message);
    }

    const notificationElement = document.createElement('div');
    notificationElement.classList.add(
      'alert',
      'alert-danger',
      notificationIdentifier
    );
    notificationElement.textContent = message;
    notificationContainerElement.appendChild(notificationElement);
  }

  renderPayPalButton() {
    if (
      !this.payPalService
        .getEligibleFundingSources()
        .filter(({ name }) => name === this.props.fundingSource).length > 0
    )
      return;

    return this.payPalService
      .getButtonExpress(this.props.fundingSource, {
        onInit: (data, actions) => this.onInit(data, actions),
        onClick: (data, actions) => this.onClick(data, actions),
        onError: (error) => this.onError(error),
        onApprove: (data, actions) => this.onApprove(data, actions),
        onCancel: (data) => this.onCancel(data),
        createOrder: (data) => this.createOrder(data)
      })
      .render(this.props.querySelector);
  }

  render() {
    this.renderPayPalButton();
    return this;
  }
}
