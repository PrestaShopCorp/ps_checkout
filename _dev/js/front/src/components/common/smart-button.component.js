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
 * @typedef SmartButtonComponentProps
 *
 * @param {string} fundingSource.name
 * @param {*}      fundingSource.mark
 *
 * @param {HTMLElement} [HTMLElement]
 * @param {HTMLElement} HTMLElementWrapper
 */

export class SmartButtonComponent extends BaseComponent {
  static Inject = {
    config: 'PsCheckoutConfig',
    payPalService: 'PayPalService',
    psCheckoutApi: 'PsCheckoutApi'
  };

  created() {
    this.data.name = this.props.fundingSource.name;
    this.data.orderId = null;

    this.data.HTMLElement = this.props.HTMLElement;

    this.data.conditions = this.app.root.children.conditionsCheckbox;
    this.data.loader = this.app.root.children.loader;
    this.data.notification = this.app.root.children.notification;
  }

  renderPayPalButton() {
    const buttonSelector = `.ps_checkout-button[data-funding-source=${this.data.name}]`;

    this.data.HTMLElement.classList.add('ps_checkout-button');
    this.data.HTMLElement.setAttribute('data-funding-source', this.data.name);

    return this.payPalService
      .getButtonPayment(this.data.name, {
        onInit: (data, actions) => {
          if (!this.data.conditions) {
            actions.enable();
            return;
          }

          if (this.data.conditions.isChecked()) {
            this.data.notification.hideConditions();
            actions.enable();
          } else {
            this.data.notification.showConditions();
            actions.disable();
          }

          this.data.conditions.onChange(() => {
            if (this.data.conditions.isChecked()) {
              this.data.notification.hideConditions();
              actions.enable();
            } else {
              this.data.notification.showConditions();
              actions.disable();
            }
          });
        },
        onClick: (data, actions) => {
          if (this.data.conditions && !this.data.conditions.isChecked()) {
            this.data.notification.hideCancelled();
            this.data.notification.hideError();
            this.data.notification.showConditions();

            return actions.reject();
          }

          if (this.data.name !== 'card') {
            this.data.loader.show();
          }

          return this.psCheckoutApi
            .postCheckCartOrder(
              {
                ...data,
                fundingSource: this.data.name,
                isExpressCheckout: this.config.expressCheckout.active,
                orderID: this.payPalService.getOrderId()
              },
              actions
            )
            .catch((error) => {
              this.data.loader.hide();
              this.data.notification.showError(error.message);
              return actions.reject();
            });
        },
        onError: (error) => {
          let errorMessage = this.handleError(error);
          console.error(error);
          this.data.loader.hide();
          this.data.notification.showError(errorMessage);

          return this.psCheckoutApi
            .postCancelOrder({
              orderID: this.data.orderId,
              fundingSource: this.data.name,
              isExpressCheckout: this.config.expressCheckout.active,
              reason: 'checkout_error',
              error: errorMessage
            })
            .catch((error) => console.error(error));
        },
        onApprove: (data, actions) => {
          this.data.loader.show();
          return this.psCheckoutApi
            .postValidateOrder(
              {
                ...data,
                fundingSource: this.data.name,
                isExpressCheckout: this.config.expressCheckout.active
              },
              actions
            )
            .catch((error) => {
              this.data.loader.hide();
              this.data.notification.showError(error.message);
            });
        },
        onCancel: (data) => {
          this.data.loader.hide();
          this.data.notification.showCanceled();

          return this.psCheckoutApi
            .postCancelOrder({
              ...data,
              fundingSource: this.data.name,
              isExpressCheckout: this.config.expressCheckout.active,
              reason: 'checkout_cancelled'
            })
            .catch((error) => {
              this.data.loader.hide();
              this.data.notification.showError(error.message);
            });
        },
        createOrder: (data) => {
          return this.psCheckoutApi
            .postCreateOrder({
              ...this.getVaultFormData(),
              ...data,
              fundingSource: this.data.name,
              isExpressCheckout: this.config.expressCheckout.active
            })
            .then((data) => {
              this.data.orderId = data;
              return data;
            })
            .catch((error) => {
              this.data.loader.hide();
              this.data.notification.showError(
                `${error.message} ${error.name}`
              );
            });
        }
      })
      .render(buttonSelector);
  }

  getVaultFormData() {
    const form = document.querySelector(`form#ps_checkout-vault-payment-form-${this.data.name}`);
    if (form) {
      const formData = new FormData(form);
      return {
        vault: formData.get(`ps_checkout-vault-payment-${this.data.name}`) === '1',
        favorite: formData.get(`ps_checkout-favorite-payment-${this.data.name}`) === '1'
      };

    }
    return {};
  }

  handleError(error) {
    let errorMessage = error;

    if (error instanceof Error) {
      if (error.message) {
        errorMessage = error.message;

        if (
          error.message.includes('CURRENCY_NOT_SUPPORTED_BY_PAYMENT_SOURCE')
        ) {
          errorMessage =
            'Provided currency is not supported by the selected payment method.';
        } else if (
          error.message.includes('COUNTRY_NOT_SUPPORTED_BY_PAYMENT_SOURCE')
        ) {
          errorMessage =
            'Provided country is not supported by the selected payment method.';
        } else if (error.message.includes('Detected popup close')) {
          errorMessage =
            'The payment failed because the payment window has been closed before the end of the payment process.';
        }
      }
    }

    return errorMessage;
  }

  render() {
    this.renderPayPalButton();
    return this;
  }
}
