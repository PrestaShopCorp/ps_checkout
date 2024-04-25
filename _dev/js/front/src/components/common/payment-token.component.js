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
import { ModalComponent } from './modal.component';
import { LoaderComponent } from './loader.component';

/**
 * @typedef PaymentTokenComponentProps
 *
 * @param {string} fundingSource.name
 *
 * @param {HTMLElement} [HTMLElement]
 * @param {HTMLElement} HTMLElementWrapper
 */

export class PaymentTokenComponent extends BaseComponent {
  static Inject = {
    config: 'PsCheckoutConfig',
    payPalService: 'PayPalService',
    psCheckoutApi: 'PsCheckoutApi',
    querySelectorService: 'QuerySelectorService',
    $: '$'
  };

  created() {
    this.data.name = this.props.fundingSource.name;
    this.data.orderId = this.payPalService.getOrderId();

    this.data.HTMLElement = this.props.HTMLElement;

    this.data.HTMLElementLabel = this.props.HTMLElementLabel;
    this.data.HTMLElementRadio = this.props.HTMLElementRadio;
    this.data.HTMLElementContainer = this.props.HTMLElementContainer;
    this.data.HTMLElementForm = this.props.HTMLElementForm;
    this.data.HTMLElementFormData = this.props.HTMLElementFormData;

    this.data.conditions = this.app.root.children.conditionsCheckbox;
    this.data.loader = this.app.root.children.loader;
    this.data.notification = this.app.root.children.notification;
    this.data.HTMLElementBaseButton =
      this.querySelectorService.getBasePaymentConfirmation();
    this.data.HTMLElementButton = null;
    this.data.HTMLElementButtonWrapper = this.getButtonWrapper();
    this.data.HTMLElementDeleteButton = this.getDeleteButton();

    this.data.disabled = false;
    this.data.modal = null;
  }

  showModal() {
    if (!this.data.modal) {
      const modalContent = document.createElement('div');
      const line1 = document.createElement('p');
      line1.innerText = this.$('checkout.payment.token.delete.modal.content');
      const paymentLabel = document.createElement('p');
      paymentLabel.innerText = this.getPaymentLabel();
      modalContent.append(line1, paymentLabel);

      const myThis = this;

      this.data.modal = new ModalComponent(this.app, {
        icon: 'delete_forever_fill',
        iconType: 'danger',
        header: this.$('checkout.payment.token.delete.modal.header'),
        content: modalContent,
        confirmText: this.$(
          'checkout.payment.token.delete.modal.confirm-button'
        ),
        confirmType: 'danger',
        onClose: () => {
          if (myThis.data.HTMLElementButton) {
            myThis.data.HTMLElementButton.removeAttribute('disabled');
          }
        },
        onCancel: () => {
          if (myThis.data.HTMLElementButton) {
            myThis.data.HTMLElementButton.removeAttribute('disabled');
          }
        },
        onConfirm: () => {
          myThis.onDeleteConfirm();
        }
      }).render();
    }
    this.data.modal.show();
  }

  onDeleteConfirm() {
    const { vaultId, fundingSource } = this.data.HTMLElementFormData;
    const loader = new LoaderComponent(this.app, {
      text: this.$('checkout.payment.loader.processing-request')
    }).render();

    loader.show();
    this.psCheckoutApi
      .postDeleteVaultedToken({ vaultId })
      .then(() => {
        this.data.disabled = true;
        this.data.HTMLElementRadio.checked = false;
        this.data.HTMLElementContainer.remove();
        this.data.HTMLElementForm.remove();
        if (fundingSource === 'paypal') {
          window.location.reload();
        } else {
          loader.destroy();
        }
      })
      .catch((error) => {
        loader.destroy();
        this.handleError(error);
      });
  }

  getDeleteButton() {
    const button = document.querySelector(`#delete-token-${this.data.HTMLElementFormData.vaultId}`);

    if (button) {
      button.addEventListener('click', (event) => {
        event.preventDefault();
        if (this.data.HTMLElementButton) {
          this.data.HTMLElementButton.setAttribute('disabled', '');
        } else {
          this.data.HTMLElement.setAttribute('disabled', '');
        }

        this.showModal();
      });
    }

    return button;
  }

  getButtonWrapper() {
    const buttonWrapper = `.ps_checkout-button[data-funding-source=${this.data.name}]`;
    return document.querySelector(buttonWrapper);
  }

  handleError(error) {
    this.data.loader.hide();
    this.data.notification.showError(error.message);

    if (this.data.HTMLElementButton) {
      this.data.HTMLElementButton.removeAttribute('disabled');
    }

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

  getPaymentLabel() {
    const form = document.querySelector(
      `form#ps_checkout-vault-token-form-${this.data.name}`
    );
    if (form) {
      const formData = new FormData(form);
      return formData.get(`ps_checkout-vault-label-${this.data.name}`);
    }
    return '';
  }

  getVaultFormData() {
    const form = document.querySelector(
      `form#ps_checkout-vault-token-form-${this.data.name}`
    );
    if (form) {
      const formData = new FormData(form);
      return {
        fundingSource: formData.get(
          `ps_checkout-funding-source-${this.data.name}`
        ),
        vaultId: formData.get(`ps_checkout-vault-id-${this.data.name}`),
        favorite:
          formData.get(`ps_checkout-favorite-payment-${this.data.name}`) === '1'
      };
    }
    return {};
  }

  createOrder() {
    this.psCheckoutApi
      .postCreateOrder(this.data.HTMLElementFormData)
      .then((data) => {
        this.data.orderId = data;
        this.redirectToPaymentPage();
      })
      .catch((error) => this.handleError(error));
  }

  redirectToPaymentPage() {
    const confirmationUrl = new URL(this.config.paymentUrl);
    confirmationUrl.searchParams.append('orderID', this.data.orderId);
    window.location.href = confirmationUrl.toString();
  }

  renderButton() {
    this.data.HTMLElementButton =
      this.data.HTMLElementBaseButton.cloneNode(true);

    this.data.HTMLElementButtonWrapper.append(this.data.HTMLElementButton);
    this.data.HTMLElementButton.classList.remove('disabled');
    this.data.HTMLElementButton.style.display = '';
    this.data.HTMLElementButton.disabled = !this.isSubmittable();

    this.data.conditions &&
      this.data.conditions.onChange(() => {
        // In some PS versions, the handler fails to disable the button because of the timing.
        setTimeout(() => {
          this.data.HTMLElementButton.disabled = !this.isSubmittable();
        }, 0);
      });

    this.data.HTMLElementButton.addEventListener('click', (event) => {
      event.preventDefault();

      this.data.loader.show();
      this.data.HTMLElementButton.setAttribute('disabled', '');

      this.createOrder();
    });
  }

  renderFavoriteImg() {
    if (
      this.data.HTMLElementLabel &&
      !document.getElementById(`ps_checkout-favorite-payment-${this.data.name}`)
    ) {
      const img = document.createElement('img');
      img.classList.add('ps-checkout', 'icon-favorite');
      img.style.display = 'inline-block';
      img.src = this.config.iconPath + 'favorite_fill.svg';

      this.data.HTMLElementLabel.append(img);
    }
  }

  isSubmittable() {
    return (
      (this.data.conditions ? this.data.conditions.isChecked() : false) &&
      !this.data.disabled
    );
  }

  renderPayPalButton() {
    const buttonSelector = `.ps_checkout-button[data-funding-source=${this.data.name}]`;

    this.data.HTMLElement.classList.add('ps_checkout-button');
    this.data.HTMLElement.setAttribute('data-funding-source', this.data.name);

    return this.payPalService
      .getButtonPayment('paypal', {
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
              ...this.data.HTMLElementFormData,
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

  render() {
    const { fundingSource } = this.data.HTMLElementFormData;
    if (fundingSource === 'paypal') {
      this.renderPayPalButton();
    } else {
      this.renderButton();
      // this.renderFavoriteImg();
    }
    return this;
  }
}
