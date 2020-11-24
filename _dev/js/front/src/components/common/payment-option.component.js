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

import { HostedFieldsComponent } from './hosted-fields.component';
import { MarkComponent } from './marker.component';
import { SmartButtonComponent } from './smart-button.component';

/**
 * @typedef PaymentOptionComponentProps
 *
 * @param {string} fundingSource.name
 * @param {*}      fundingSource.mark
 *
 * @param {HTMLElement} HTMLElement
 * @param {HTMLElement} [HTMLElementWrapper]
 * @param {HTMLElement} [HTMLElementLabel]
 * @param {HTMLElement} [HTMLElementMark]
 * @param {HTMLElement} [HTMLElementHostedFields]
 * @param {HTMLElement} [HTMLElementSmartButtonWrapper]
 */

export class PaymentOptionComponent extends BaseComponent {
  static INJECT = {
    config: 'config',
    htmlElementService: 'htmlElementService',
    $: '$'
  };

  /**
   * @param checkout
   * @param props
   */
  constructor(checkout, props) {
    super(checkout, props);

    this.data.name = props.fundingSource.name;

    this.data.HTMLElement = props.HTMLElement;
    this.data.HTMLElementWrapper = this.getWrapper();
    this.data.HTMLElementLabel = this.getLabel();
    this.data.HTMLElementMark = props.HTMLElementMark || null;

    this.data.HTMLElementHostedFields = this.getHostedFields();
    this.data.HTMLElementSmartButton = this.getSmartButton();
  }

  getHostedFields() {
    const hostedFieldsFormId = 'ps_checkout-hosted-fields-form';
    return (
      this.data.name === 'card' &&
      (this.props.HTMLElementHostedFields ||
        document.getElementById(hostedFieldsFormId))
    );
  }

  getLabel() {
    const translationKey = `funding-source.name.${this.data.name}`;
    const label =
      this.$(translationKey) !== undefined
        ? this.$(translationKey)
        : this.$('funding-source.name.default');

    return Array.prototype.slice
      .call(this.data.HTMLElementWrapper.querySelectorAll('*'))
      .find((item) => item.innerHTML.trim() === label.trim());
  }

  getSmartButton() {
    const smartButtonSelector = `.ps_checkout-button[data-funding-source=${this.data.name}]`;
    return (
      this.props.HTMLElementSmartButton ||
      document.querySelector(smartButtonSelector)
    );
  }

  getWrapper() {
    const wrapperId = `${this.data.HTMLElement.id}-container`;
    return this.props.HTMLElementWrapper || document.getElementById(wrapperId);
  }

  onLabelClick(listener) {
    this.data.HTMLElementLabel.addEventListener('click', (event) => {
      event.preventDefault();
      listener(this, event);
    });
  }

  renderWrapper() {
    this.data.HTMLElementWrapper.classList.add('ps_checkout-payment-option');
    this.data.HTMLElementWrapper.style.display = '';
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

  render() {
    this.renderWrapper();
    this.renderMark();

    if (this.data.HTMLElementHostedFields) {
      this.children.hostedFields = new HostedFieldsComponent(this.app, {
        fundingSource: this.props.fundingSource,

        HTMLElement: this.data.HTMLElementHostedFields
      }).render();
    } else {
      this.children.smartButton = new SmartButtonComponent(this.app, {
        fundingSource: this.props.fundingSource,

        HTMLElement: this.data.HTMLElementSmartButton
      }).render();
    }

    return this;
  }
}
