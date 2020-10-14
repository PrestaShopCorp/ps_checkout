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
const PAYMENT_OPTION_CONTAINER_IDENTIFIER = id => `${id}-container`;

let BASE_PAYMENT_OPTION;
let BASE_PAYMENT_OPTION_ID;
let BASE_PAYMENT_OPTION_CONTAINER;
let BASE_PAYMENT_OPTION_FORM;
let BASE_PAYMENT_OPTION_ADDITIONAL_INFORMATION;

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

    this.children = {};
  }

  setBasePaymentOption() {
    if (BASE_PAYMENT_OPTION) return;

    BASE_PAYMENT_OPTION = this.htmlElementService
      .getAnyPaymentOption()
      .cloneNode(true);

    BASE_PAYMENT_OPTION_ID = BASE_PAYMENT_OPTION.id || null;

    // perez-furio.ext: If this happens, global error (No Payment Option available)?
    BASE_PAYMENT_OPTION_CONTAINER = this.htmlElementService
      .getPaymentOptionContainer(BASE_PAYMENT_OPTION_ID)
      .cloneNode(true);

    BASE_PAYMENT_OPTION_FORM = this.htmlElementService
      .getPaymentOptionFormContainer(BASE_PAYMENT_OPTION_ID)
      .cloneNode(true);

    BASE_PAYMENT_OPTION_ADDITIONAL_INFORMATION = this.htmlElementService
      .getPaymentOptionAdditionalInformation(BASE_PAYMENT_OPTION_ID)
      .cloneNode(true);

    this.htmlElementService.getAnyPaymentOption().remove();
    this.htmlElementService
      .getPaymentOptionContainer(BASE_PAYMENT_OPTION_ID)
      .remove();
    this.htmlElementService
      .getPaymentOptionFormContainer(BASE_PAYMENT_OPTION_ID)
      .remove();
    this.htmlElementService
      .getPaymentOptionAdditionalInformation(BASE_PAYMENT_OPTION_ID)
      .remove();
  }

  getPaymentOptionId() {
    return BASE_PAYMENT_OPTION_ID + '-' + this.fundingSource.name;
  }

  getPaymentOptionLabel() {
    return undefined !==
      this.$(`funding-source.name.${this.fundingSource.name}`)
      ? this.$(`funding-source.name.${this.fundingSource.name}`)
      : this.$('funding-source.name.default');
  }

  renderNewPaymentOptionLabel() {
    this.paymentOption = this.htmlElementService.getPaymentOption(
      this.paymentOptionContainer
    );

    this.paymentOption.id = this.getPaymentOptionId();

    const containerLabel = this.htmlElementService.getPaymentOptionLabel(
      this.paymentOptionContainer,
      BASE_PAYMENT_OPTION_ID
    );

    const newContainerLabel = document.createElement('label');
    newContainerLabel.htmlFor = `${BASE_PAYMENT_OPTION_ID}-${this.fundingSource.name}`;

    const newContainerLabelSpan = document.createElement('span');
    newContainerLabelSpan.innerText = this.getPaymentOptionLabel();

    const newContainerLabelMark = document.createElement('div');
    newContainerLabelMark.style.display = 'inline-block';
    newContainerLabelMark.id = PAYMENT_OPTION_LABEL_MARK(
      this.fundingSource.name
    );

    newContainerLabelSpan.append(newContainerLabelMark);
    newContainerLabel.append(newContainerLabelSpan);
    containerLabel.replaceWith(newContainerLabel);
  }

  renderNewPaymentOptionContainer() {
    this.paymentOptionContainer = BASE_PAYMENT_OPTION_CONTAINER.cloneNode(true);

    this.paymentOptionContainer.style.display = 'block';
    this.paymentOptionContainer.id = PAYMENT_OPTION_CONTAINER_IDENTIFIER(
      this.getPaymentOptionId()
    );

    let paymentOptionSelect = this.htmlElementService.getPaymentOptionSelect(
      this.paymentOptionContainer
    );
    paymentOptionSelect.value = this.getPaymentOptionId();

    this.renderNewPaymentOptionLabel();
  }

  renderNewPaymentOptionContainerForm() {
    this.paymentOptionFormContainer = BASE_PAYMENT_OPTION_FORM.cloneNode(true);

    const paymentOptionFormButton = this.htmlElementService.getPaymentOptionFormButton(
      this.paymentOptionFormContainer,
      BASE_PAYMENT_OPTION_ID
    );

    this.paymentOptionFormContainer.id = `pay-with-${this.getPaymentOptionId()}-form`;
    paymentOptionFormButton.id = `pay-with-${this.getPaymentOptionId()}`;
  }

  renderNewPaymentOption() {
    this.renderNewPaymentOptionContainer();
    this.renderNewPaymentOptionContainerForm();

    this.paymentOptionsContainer = this.htmlElementService.getPaymentOptionsContainer();
    this.paymentOptionsContainer.append(this.paymentOptionContainer);
    this.paymentOptionsContainer.append(this.paymentOptionFormContainer);

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
      this.paymentOptionAdditionalInformation = BASE_PAYMENT_OPTION_ADDITIONAL_INFORMATION.cloneNode(
        true
      );

      this.children.hostedFields = new HostedFieldsComponent(
        this.checkout,
        this,
        this.fundingSource
      ).render();
    } else {
      this.children.smartButton = new SmartButtonComponent(
        this.checkout,
        this.fundingSource
      ).render();
    }
  }

  render() {
    if (this.fundingSource) {
      this.renderNewPaymentOption();
      this.renderNewPaymentOptionChildren();
    }

    this.paymentOption.addEventListener('change', () => {
      this.checkout.children.notification.hideCancelled();
      this.checkout.children.notification.hideError();
      this.checkout.children.paymentOptions.children.paymentOptions.forEach(
        ({ children }) => {
          if (children.smartButton) {
            children.smartButton.hide();
          }
          if (children.hostedFields) {
            children.hostedFields.hide();
          }
        }
      );

      this.children.smartButton && this.children.smartButton.show();
      this.children.hostedFields && this.children.hostedFields.show();
    });

    return this;
  }

  isDefaultPaymentOption() {
    return !this.fundingSource;
  }
}
