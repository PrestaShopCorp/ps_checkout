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
import { SmartButtonComponent } from './smart-button.component';
import { PaymentFieldsComponent } from "./payment-fields.component";
import {CardFieldsComponent} from "./card-fields.component";
import {PS_VERSION_1_6} from "../../constants/ps-version.constants";
import {PaymentTokenComponent} from "./payment-token.component";

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
    payPalService: 'PayPalService',
    querySelectorService: 'QuerySelectorService',
    prestashopService: 'PrestashopService',
    $: '$'
  };

  created() {
    this.data.name = this.props.fundingSource.name;

    this.data.HTMLElement = this.props.HTMLElement;
    this.data.HTMLElementContainer = this.getContainer();
    this.data.HTMLElementLabel = this.getLabel();
    this.data.HTMLElementMark = this.props.HTMLElementMark || null;

    this.data.HTMLElementCardFields =this.querySelectorService.getCardFieldsFormContainer();
    this.data.HTMLElementSmartButton = this.getSmartButton();
    this.data.HTMLElementPaymentFields = this.getPaymentFields();
  }

  getContainer() {
    const wrapperId = `${this.data.HTMLElement.id}-container`;
    return document.getElementById(wrapperId);
  }

  getPaymentFields() {
    const container = `pay-with-${this.data.HTMLElement.id}-form`;
    const APM = ['bancontact', 'blik', 'eps', 'giropay', 'ideal', 'mybank', 'p24', 'sofort'];

    const APMEligible = typeof this.payPalService.sdk.PaymentFields?.isEligible === "function" ?
      this.payPalService.sdk.PaymentFields.isEligible(this.data.name)
      : APM.includes(this.data.name)

    return (
      APMEligible &&
      document.getElementById(container)
    );
  }

  getLabel() {
    const translationKey = `funding-source.name.${this.data.name}`;
    const label =
      this.$(translationKey) !== undefined
        ? this.$(translationKey)
        : this.$('funding-source.name.default');

    let element = Array.prototype.slice
      .call(this.data.HTMLElementContainer.querySelectorAll('*'))
      .find(
        item => (this.prestashopService.getVersion() === PS_VERSION_1_6 ? item.innerHTML.trim() : item.innerText.trim())
          === label.trim()
      );

    if (!element) {
      console.error('HTMLElement label "' + label.trim() + '" not found.');
    }

    return element;
  }

  getSmartButton() {
    const smartButtonSelector = `.ps_checkout-button[data-funding-source=${this.data.name}]`;
    return document.querySelector(smartButtonSelector);
  }

  onLabelClick(listener) {
    this.data.HTMLElementLabel.addEventListener('click', event => {
      event.preventDefault();
      listener(this, event);
    });
  }

  renderWrapper() {
    this.data.HTMLElementContainer.classList.add('ps_checkout-payment-option');
    this.data.HTMLElementContainer.style.display = '';
  }

  renderMark() {
    if (!this.data.HTMLElementLabel) {
      return;
    }

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

  renderPaymentFields() {
    if (!this.data.HTMLElementPaymentFields) {
      return;
    }

    this.children.PaymentFields = this.PaymentFields = new PaymentFieldsComponent(this.app, {
      fundingSource: this.props.fundingSource,

      HTMLElement: this.data.HTMLElementPaymentFields
    }).render();
  }

  render() {
    if (this.data.HTMLElementContainer.classList.contains('ps_checkout-payment-option')) {
      // Render already done
      return;
    }

    this.renderWrapper();
    this.renderMark();
    this.renderPaymentFields();

    const isCardFieldsEligible = this.payPalService.isCardFieldsEligible();
    // Check if all fields required for cardFields are present in DOM
    const isCardFieldsAvailable = this.data.name === 'card'
      && this.config.hostedFieldsEnabled
      && this.querySelectorService.getCardFieldsNameInputContainer()
      && this.querySelectorService.getCardFieldsNumberInputContainer()
      && this.querySelectorService.getCardFieldsExpiryInputContainer()
      && this.querySelectorService.getCardFieldsCvvInputContainer();

    if (this.data.HTMLElementCardFields && (!isCardFieldsEligible || !isCardFieldsAvailable)) {
      this.data.HTMLElementCardFields.style.display = 'none';
    }

    if (this.props.fundingSource.name.includes('token')) {
      this.children.paymentToken = new PaymentTokenComponent(this.app, {
        fundingSource: this.props.fundingSource,
        HTMLElement: this.data.HTMLElementSmartButton
      }).render();
    } else if (this.data.HTMLElementCardFields && isCardFieldsEligible && isCardFieldsAvailable) {
      this.data.HTMLElementCardFields.style.display = '';
      this.children.cardFields = new CardFieldsComponent(this.app, {
        fundingSource: this.props.fundingSource,
        HTMLElement: this.data.HTMLElementCardFields
      }).render();
    } else {
      this.children.smartButton = new SmartButtonComponent(this.app, {
        fundingSource: this.props.fundingSource,

        HTMLElement: this.data.HTMLElementSmartButton
      }).render();
    }

    window.ps_checkout.events.dispatchEvent(
      new CustomEvent('payment-option-active', {
        detail: {
          fundingSource: this.data.name,
          HTMLElement: this.data.HTMLElement,
          HTMLElementContainer: this.data.HTMLElementContainer,
          HTMLElementBinary: this.data.HTMLElementCardFields && isCardFieldsEligible && isCardFieldsAvailable
            ? this.children.cardFields.data.HTMLElementButton.parentElement
            : this.data.HTMLElementSmartButton
        }
      })
    );

    return this;
  }
}
