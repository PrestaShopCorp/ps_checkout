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
import { SmartButtonComponent } from '../common/smart-button.component';
import { HostedFieldsComponent } from '../common/hosted-fields.component';
import { MarkerComponent } from '../common/marker.component';

const PAYMENT_OPTION_LABEL_MARK = id => `${id}-mark`;
// const PAYMENT_OPTION_CONTAINER_IDENTIFIER = id => `${id}-container`;

let BASE_PAYMENT_OPTION;

export class PaymentOptionComponent {
  constructor(checkout, fundingSource, paymentOption) {
    this.checkout = checkout;
    this.checkoutConfig = checkout.config;

    this.config = this.checkout.config;

    this.htmlElementService = checkout.htmlElementService;
    this.payPalService = checkout.payPalService;

    this.fundingSource = fundingSource;
    this.paymentOption = paymentOption;

    this.$ = this.checkout.$;

    this.setBasePaymentOption();

    this.open = false;
    this.children = {};
  }

  setBasePaymentOption() {
    if (BASE_PAYMENT_OPTION) return;

    BASE_PAYMENT_OPTION = this.htmlElementService
      .getAnyPaymentOption()
      .cloneNode(true);

    this.htmlElementService.getAnyPaymentOption().remove();
  }

  getPaymentOptionId() {
    return `option-' + ${this.fundingSource.name}`;
  }

  getPaymentOptionLabel() {
    return undefined !==
      this.$(`funding-source.name.${this.fundingSource.name}`)
      ? this.$(`funding-source.name.${this.fundingSource.name}`)
      : this.$('funding-source.name.default');
  }

  renderNewPaymentOptionLabel() {
    const newContainerLabelMark = document.createElement('div');
    newContainerLabelMark.style.display = 'inline-block';
    newContainerLabelMark.id = PAYMENT_OPTION_LABEL_MARK(
      this.fundingSource.name
    );

    this.paymentOptionAnchor = document.createElement('a');
    this.paymentOptionAnchor.classList.add(
      `pscheckout-${
        this.fundingSource.name === 'card' ? this.fundingSource.name : 'paypal'
      }`
    );
    this.paymentOptionAnchor.innerText = this.getPaymentOptionLabel();
    this.paymentOptionAnchor.setAttribute('href', '#');

    this.paymentOptionAnchor.prepend(newContainerLabelMark);
    this.paymentOptionContainer.append(this.paymentOptionAnchor);
  }

  renderNewPaymentOptionData() {
    this.dataContainer = document.createElement('a');
    this.paymentOptionData.append(this.dataContainer);
  }

  renderNewPaymentOptionContainer() {
    this.paymentOptionContainer = document.createElement('p');
    this.paymentOptionContainer.classList.add('payment_module', 'closed');

    this.renderNewPaymentOptionLabel();

    this.paymentOptionData = document.createElement('p');
    this.paymentOptionData.classList.add('payment_module', 'closed');

    this.renderNewPaymentOptionData();

    this.sourcePaymentOptionContainer = this.htmlElementService.getPaymentOptionContainer(
      this.paymentOption
    );
    this.sourcePaymentOptionContainer.replaceWith(this.paymentOptionContainer);

    this.paymentOptionContainer.parentNode.append(this.paymentOptionData);
  }

  renderNewPaymentOption() {
    this.paymentOption = BASE_PAYMENT_OPTION.cloneNode(true);
    this.paymentOption.style.display = '';

    this.renderNewPaymentOptionContainer();

    this.checkoutPaymentOptionsContainer = this.htmlElementService.getCheckoutPaymentOptionsContainer();
    this.checkoutPaymentOptionsContainer.prepend(this.paymentOption);

    this.marker = new MarkerComponent(
      this,
      this.fundingSource,
      `#${PAYMENT_OPTION_LABEL_MARK(this.fundingSource.name)}`
    ).render();
  }

  renderNewPaymentOptionChildren() {
    if (
      this.fundingSource.name === 'card' &&
      this.payPalService.isHostedFieldsEligible() &&
      this.config.expressCheckoutHostedFieldsEnabled
    ) {
      this.dataContainer.append(...this.sourcePaymentOptionContainer.children);
      this.dataContainer.children.forEach(el => (el.style.display = ''));

      this.children.hostedFields = new HostedFieldsComponent(
        this.checkout,
        this,
        this.fundingSource,
        this.dataContainer,
        this.dataContainer
      ).render();
    } else {
      this.children.smartButton = new SmartButtonComponent(
        this.checkout,
        this.fundingSource,
        this.dataContainer
      ).render();
    }
  }

  render() {
    if (this.fundingSource) {
      this.renderNewPaymentOption();
      this.renderNewPaymentOptionChildren();
    }

    // Ignore since in 1.6 we don't need to do anything with default payment options
    return this;
  }

  isDefaultPaymentOption() {
    return !this.fundingSource;
  }

  isOpen() {
    return this.open;
  }

  setOpen(value) {
    if (value) {
      this.paymentOptionContainer.classList.add('open');
      this.paymentOptionData.classList.add('open');
      this.paymentOptionContainer.classList.remove('closed');
      this.paymentOptionData.classList.remove('closed');
    } else {
      this.paymentOptionContainer.classList.add('closed');
      this.paymentOptionData.classList.add('closed');
      this.paymentOptionContainer.classList.remove('open');
      this.paymentOptionData.classList.remove('open');
    }

    this.open = value;
  }

  onClick(listener) {
    this.paymentOptionAnchor.addEventListener('click', event => {
      event.preventDefault();
      listener(this, event);
    });
  }
}
