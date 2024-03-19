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
import {ModalComponent} from "./modal.component";

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

    this.data.HTMLElementRadio = this.props.HTMLElementRadio;
    this.data.HTMLElementContainer = this.props.HTMLElementContainer;
    this.data.HTMLElementForm = this.props.HTMLElementForm;

    this.data.conditions = this.app.root.children.conditionsCheckbox;
    this.data.loader = this.app.root.children.loader;
    this.data.notification = this.app.root.children.notification;
    this.data.HTMLElementBaseButton = this.querySelectorService.getBasePaymentConfirmation();
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

      this.data.modal = new ModalComponent(this.app, {
        header: this.$('checkout.payment.token.delete.modal.header'),
        content: modalContent,
        confirmText: this.$('checkout.payment.token.delete.modal.confirm-button'),
        confirmType: 'danger',
        onClose: (() => {
          this.data.HTMLElementButton.removeAttribute('disabled');
        }),
        onConfirm: (() => {this.onDeleteConfirm()})
      }).render();
    }
    this.data.modal.show();
  }

  onDeleteConfirm() {
    const vaultId = this.getVaultFormData().vaultId;
    this.psCheckoutApi.postDeleteVaultedToken({vaultId}).then(() => {
      this.data.disabled = true;
      this.data.HTMLElementRadio.setAttribute('disabled', '');
      this.data.HTMLElementRadio.classList.add('disabled');
      this.data.HTMLElementContainer.style.display = 'none';
      this.data.HTMLElementForm.style.display = 'none';
    }).catch((error) => this.handleError(error));
  }

  getDeleteButton() {
    const button = document.querySelector(`#delete-${this.data.name}`);

    button.addEventListener('click', (event) => {
      event.preventDefault();
      this.data.HTMLElementButton.setAttribute('disabled', '');
      this.showModal();
    });

    return button;
  }

  getButtonWrapper() {
    const buttonWrapper = `.ps_checkout-button[data-funding-source=${this.data.name}]`;
    return document.querySelector(buttonWrapper);
  }

  handleError(error) {
    this.data.loader.hide();
    this.data.notification.showError(error.message);
    this.data.HTMLElementButton.removeAttribute('disabled');
  }

  submitOrder() {
    this.createOrder().then(() => this.validateOrder());
  }

  getPaymentLabel() {
    const form = document.querySelector(`form#ps_checkout-vault-token-form-${this.data.name}`);
    if (form) {
      const formData = new FormData(form);
      return formData.get(`ps_checkout-vault-label-${this.data.name}`);
    }
    return '';
  }

  getVaultFormData() {
    const form = document.querySelector(`form#ps_checkout-vault-token-form-${this.data.name}`);
    if (form) {
      const formData = new FormData(form);
      return {
        fundingSource: formData.get(`ps_checkout-funding-source-${this.data.name}`),
        vaultId: formData.get(`ps_checkout-vault-id-${this.data.name}`),
        favorite: formData.get(`ps_checkout-favorite-payment-${this.data.name}`) === '1'
      };
    }
    return {};
  }

  createOrder() {
    return this.psCheckoutApi.postCreateOrder(this.getVaultFormData())
      .then((data) => {
        this.data.orderId = data;
        this.validateOrder();
      })
      .catch((error) => this.handleError(error));
  }

  validateOrder() {
    this.psCheckoutApi.postValidateOrder(
      {
        orderID: this.data.orderId,
        fundingSource: this.data.name,
      }
    ).then(() => {
      this.data.loader.hide();
      this.data.HTMLElementButton.removeAttribute('disabled');
    }).catch((error) => this.handleError(error));
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

      this.submitOrder();
    });
  }

  isSubmittable() {
    return (this.data.conditions ? this.data.conditions.isChecked() : false) && !this.data.disabled;
  }

  render() {
    this.renderButton();
    return this;
  }
}
