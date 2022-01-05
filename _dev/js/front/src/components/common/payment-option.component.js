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

import { MarkComponent } from './marker.component';

/**
 * @typedef PaymentOptionComponentProps
 *
 * @param {string} fundingSource.name
 * @param {*}      fundingSource.mark
 *
 * @param {HTMLElement} HTMLElement
 */
export class PaymentOptionComponent extends BaseComponent {
  static Inject = {
    config: 'PsCheckoutConfig',
    psCheckoutApi: 'PsCheckoutApi',
    psCheckoutService: 'PsCheckoutService',
    $: '$'
  };

  created() {
    this.data.name = this.props.fundingSource.name;

    this.data.HTMLElement = this.props.HTMLElement;
    this.data.HTMLElementBaseButton = this.getBaseButton();
    this.data.HTMLElementButton = null;
    this.data.HTMLElementButtonWrapper = this.getButtonWrapper();
    this.data.HTMLElementContainer = this.getContainer();
    this.data.HTMLElementLabel = this.getLabel();
    this.data.HTMLElementMark = this.props.HTMLElementMark || null;
  }

  getBaseButton() {
    const buttonSelector = `#payment-confirmation button`;
    return document.querySelector(buttonSelector);
  }

  getButtonWrapper() {
    const buttonWrapper = `.ps_checkout-button[data-funding-source=${this.data.name}]`;
    return document.querySelector(buttonWrapper);
  }

  getContainer() {
    const wrapperId = `${this.data.HTMLElement.id}-container`;
    return document.getElementById(wrapperId);
  }

  getLabel() {
    const translationKey = `funding-source.name.${this.data.name}`;
    const label =
      this.$(translationKey) !== undefined
        ? this.$(translationKey)
        : this.$('funding-source.name.default');

    return Array.prototype.slice
      .call(this.data.HTMLElementContainer.querySelectorAll('*'))
      .find((item) => item.innerHTML.trim() === label.trim());
  }

  onLabelClick(listener) {
    this.data.HTMLElementLabel.addEventListener('click', (event) => {
      event.preventDefault();
      listener(this, event);
    });
  }

  renderWrapper() {
    this.data.HTMLElementContainer.classList.add('ps_checkout-payment-option');
    this.data.HTMLElementContainer.style.display = '';
  }

  renderMark() {
    if (!this.data.HTMLElementMarker) {
      this.data.HTMLElementMarker = document.createElement('div');
      this.data.HTMLElementMarker.style.display = 'inline-block';

      if (this.props.markPosition === 'before') {
        this.data.HTMLElementLabel.prepend(this.data.HTMLElementMarker);
      } else {
        this.data.HTMLElementLabel.append(this.data.HTMLElementMarker);
      }
    }

    this.children.Marker = this.marker = new MarkComponent(this.app, {
      fundingSource: this.props.fundingSource,

      HTMLElement: this.data.HTMLElementMarker
    }).render();
  }

  renderButton() {
    this.data.HTMLElementButton =
      this.data.HTMLElementBaseButton.cloneNode(true);

    this.data.HTMLElementButtonWrapper.append(this.data.HTMLElementButton);
    this.data.HTMLElementButton.disabled = !this.data.conditions.isChecked();

    this.data.conditions &&
      this.data.conditions.onChange(() => {
        this.data.HTMLElementButton.disabled =
          !this.data.conditions.isChecked();
      });
  }

  render() {
    this.data.conditions = this.app.root.children.conditionsCheckbox;

    this.renderWrapper();
    this.renderMark();
    // this.renderButton();

    const form = document
      .querySelector(`form[action^="javascript:void('PS_CHECKOUT/${this.data.name}"]`);

    const submitButton = document
      .querySelector('#payment-confirmation button');

    window.$(form).submit(() => {
      if (submitButton) {
        submitButton.removeAttribute('disabled')
        submitButton.classList.remove('disabled');
      }

      if (window.ps_checkout.config.hostedFields.enabled && this.data.name === "card") {
        return window['csdk'].CheckoutHostedFields.render({
          paypal: window.ps_checkout.config.paypal,

          hostedFields: {
            onOrderCreate: () => this.psCheckoutApi
                .postCreateOrder({
                  fundingSource: this.data.name,
                  isHostedFields: true
                }),

            onContingencyValidation: (data) => {
              const { liabilityShifted, authenticationReason } = data;

              return this.psCheckoutService
                .validateContingency(liabilityShifted, authenticationReason)
                .then(() => {
                  // Backend requirement
                  data.orderID = data.orderId;
                  delete data.orderId;

                  return this.psCheckoutApi.postValidateOrder({
                    ...data,
                    fundingSource: this.data.name,
                    isHostedFields: true
                  });
                });
            }
          },
        });
      }

      return window['csdk'].CheckoutPaymentButton.render({
        paypal: window.ps_checkout.config.paypal,

        paymentButton: {
          fundingSource: this.data.name,
          ...(window.ps_checkout.config.paymentButton || {}),
          onOrderCreate: (data) => this.psCheckoutApi
            .postCreateOrder({
              ...data,
              fundingSource: this.data.name
            }),

          onClick: (data) => this.psCheckoutApi
            .postCheckCartOrder(
              { ...data, fundingSource: this.data.name }
            ),

          onApprove: (data) => this.psCheckoutApi
            .postValidateOrder(
              { ...data, fundingSource: this.data.name }
            ),
        },
      });
    });

    window.ps_checkout.events.dispatchEvent(
      new CustomEvent('payment-option-active', {
        detail: {
          // fundingSource: this.data.name,
          // HTMLElement: this.data.HTMLElement,
          // HTMLElementContainer: this.data.HTMLElementContainer,
          // HTMLElementBinary: this.data.HTMLElementHostedFields
          //   ? this.children.hostedFields.data.HTMLElementButton.parentElement
          //   : this.data.HTMLElementSmartButton
        }
      })
    );

    return this;
  }
}
