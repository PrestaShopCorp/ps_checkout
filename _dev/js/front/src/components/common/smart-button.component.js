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
import { BaseComponent } from '../../core/base.component';

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
  static INJECT = {
    config: 'config',
    htmlElementService: 'htmlElementService',
    payPalService: 'payPalService',
    psCheckoutService: 'psCheckoutService'
  };

  constructor(app, props) {
    super(app, props);

    this.data.name = props.fundingSource.name;

    this.data.HTMLElement = props.HTMLElement;

    this.data.conditionsComponent = this.app.children.conditionsCheckbox;
    this.data.loaderComponent = this.app.children.loader;
    this.data.notificationComponent = this.app.children.notification;
  }

  renderPayPalButton() {
    const buttonSelector = `.ps_checkout-button[data-funding-source=${this.data.name}]`;

    this.data.HTMLElement.classList.add('ps_checkout-button');
    this.data.HTMLElement.setAttribute('data-funding-source', this.data.name);

    return this.payPalService
      .getButtonPayment(this.data.name, {
        onInit: (data, actions) => {
          if (!this.data.conditionsComponent) {
            actions.enable();
            return;
          }

          if (this.data.conditionsComponent.isChecked()) {
            this.data.notificationComponent.hideConditions();
            actions.enable();
          } else {
            this.data.notificationComponent.showConditions();
            actions.disable();
          }

          this.data.conditionsComponent.onChange(() => {
            if (this.data.conditionsComponent.isChecked()) {
              this.data.notificationComponent.hideConditions();
              actions.enable();
            } else {
              this.data.notificationComponent.showConditions();
              actions.disable();
            }
          });
        },
        onClick: (data, actions) => {
          if (
            this.data.conditionsComponent &&
            !this.data.conditionsComponent.isChecked()
          ) {
            this.data.notificationComponent.hideCancelled();
            this.data.notificationComponent.hideError();
            this.data.notificationComponent.showConditions();

            return;
          }

          if (this.data.name !== 'card') {
            this.data.loaderComponent.show();
          }

          this.psCheckoutService
            .postCheckCartOrder(
              { ...data, fundingSource: this.data.name },
              actions
            )
            .catch((error) => {
              this.data.loaderComponent.hide();
              this.data.notificationComponent.showError(error.message);
              actions.reject();
            });
        },
        onError: (error) => {
          console.error(error);
          this.data.loaderComponent.hide();
          this.data.notificationComponent.showError(
            error instanceof TypeError ? error.message : ''
          );
        },
        onApprove: (data, actions) => {
          this.data.loaderComponent.show();
          return this.psCheckoutService
            .postValidateOrder(
              { ...data, fundingSource: this.data.name },
              actions
            )
            .catch((error) => {
              this.data.loaderComponent.hide();
              this.data.notificationComponent.showError(error.message);
            });
        },
        onCancel: (data) => {
          this.data.loaderComponent.hide();
          this.data.notificationComponent.showCanceled();

          return this.psCheckoutService
            .postCancelOrder({
              ...data,
              fundingSource: this.data.name
            })
            .catch((error) => {
              this.data.loaderComponent.hide();
              this.data.notificationComponent.showError(error.message);
            });
        },
        createOrder: (data) => {
          return this.psCheckoutService
            .postCreateOrder({
              ...data,
              fundingSource: this.data.name
            })
            .catch((error) => {
              this.data.loaderComponent.hide();
              this.data.notificationComponent.showError(
                `${error.message} ${error.name}`
              );
            });
        }
      })
      .render(buttonSelector);
  }

  render() {
    this.renderPayPalButton();
    return this;
  }
}
