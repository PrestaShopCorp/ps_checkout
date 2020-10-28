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
import { SMART_BUTTON_CLASS } from '../../constants/ps-checkout-classes.constants';

export class SmartButtonComponent {
  constructor(checkout, fundingSource, htmlContainer) {
    this.checkout = checkout;
    this.checkoutConfig = checkout.config;

    this.fundingSource = fundingSource;

    this.htmlElementService = checkout.htmlElementService;
    this.payPalService = checkout.payPalService;
    this.psCheckoutService = checkout.psCheckoutService;

    this.buttonContainer =
      htmlContainer || this.htmlElementService.getButtonContainer();
  }

  getButtonId() {
    return `button-${this.fundingSource.name}`;
  }

  renderPayPalButton() {
    return this.payPalService
      .getButtonPayment(this.fundingSource.name, {
        onInit: (data, actions) => {
          if (!this.checkout.children.conditionsCheckbox) {
            actions.enable();
            return;
          }

          if (this.checkout.children.conditionsCheckbox.isChecked()) {
            this.checkout.children.notification.hideConditions();
            actions.enable();
          } else {
            this.checkout.children.notification.showConditions();
            actions.disable();
          }

          this.checkout.children.conditionsCheckbox.onChange(() => {
            if (this.checkout.children.conditionsCheckbox.isChecked()) {
              this.checkout.children.notification.hideConditions();
              actions.enable();
            } else {
              this.checkout.children.notification.showConditions();
              actions.disable();
            }
          });
        },
        onClick: (data, actions) => {
          if (
            this.checkout.children.conditionsCheckbox &&
            !this.checkout.children.conditionsCheckbox.isChecked()
          ) {
            this.checkout.children.notification.hideCancelled();
            this.checkout.children.notification.hideError();
            this.checkout.children.notification.showConditions();

            return;
          }

          if (this.fundingSource.name !== 'card') {
            this.checkout.children.loader.show();
          }

          this.psCheckoutService
            .postCheckCartOrder(
              { ...data, fundingSource: this.fundingSource.name },
              actions
            )
            .catch(error => {
              this.checkout.children.loader.hide();
              this.checkout.children.notification.showError(error.message);
              actions.reject();
            });
        },
        onError: error => {
          console.error(error);
          this.checkout.children.loader.hide();
          this.checkout.children.notification.showError(
            error instanceof TypeError ? error.message : ''
          );
        },
        onApprove: (data, actions) => {
          this.checkout.children.loader.show();
          return this.psCheckoutService
            .postValidateOrder(
              { ...data, fundingSource: this.fundingSource.name },
              actions
            )
            .catch(error => {
              this.checkout.children.loader.hide();
              this.checkout.children.notification.showError(error.message);
            });
        },
        onCancel: data => {
          this.checkout.children.loader.hide();
          this.checkout.children.notification.showCanceled();

          return this.psCheckoutService
            .postCancelOrder({
              ...data,
              fundingSource: this.fundingSource.name
            })
            .catch(error => {
              this.checkout.children.loader.hide();
              this.checkout.children.notification.showError(error.message);
            });
        },
        createOrder: data => {
          return this.psCheckoutService
            .postCreateOrder({
              ...data,
              fundingSource: this.fundingSource.name
            })
            .catch(error => {
              this.checkout.children.loader.hide();
              this.checkout.children.notification.showError(
                `${error.message} ${error.name}`
              );
            });
        }
      })
      .render(`#${this.getButtonId()}`);
  }

  render() {
    this.smartButton = document.createElement('div');

    this.smartButton.id = this.getButtonId();
    this.smartButton.classList.add(SMART_BUTTON_CLASS);

    this.buttonContainer.append(this.smartButton);

    this.renderPayPalButton();

    return this;
  }

  show() {
    this.smartButton.style.display = 'block';
  }

  hide() {
    this.smartButton.style.display = 'none';
  }
}
