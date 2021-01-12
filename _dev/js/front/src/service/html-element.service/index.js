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
import {
  PS_VERSION_1_6,
  PS_VERSION_1_7
} from '../../constants/ps-version.constants';
import { BaseClass } from '../../core/dependency-injection/base.class';

import { HtmlElementPs1_6Service } from './html-element-ps1_6.service';
import { HtmlElementPs1_7Service } from './html-element-ps1_7.service';

export class HTMLElementService extends BaseClass {
  static Inject = {
    prestashopService: 'PrestashopService'
  };

  constructor(app) {
    super(app);

    this.instance = new {
      [PS_VERSION_1_6]: HtmlElementPs1_6Service,
      [PS_VERSION_1_7]: HtmlElementPs1_7Service
    }[this.prestashopService.getVersion()](app);
  }

  getBasePaymentOption() {
    return this.instance.getBasePaymentOption();
  }

  getButtonContainer() {
    return this.instance.getButtonContainer();
  }

  getCheckoutExpressCartButtonContainer() {
    return this.instance.getCheckoutExpressCartButtonContainer();
  }

  getCheckoutExpressCheckoutButtonContainer() {
    return this.instance.getCheckoutExpressCheckoutButtonContainer();
  }

  getCheckoutExpressProductButtonContainer() {
    return this.instance.getCheckoutExpressProductButtonContainer();
  }

  getConditionsCheckboxContainer() {
    return this.instance.getConditionsCheckboxContainer();
  }

  getConditionsCheckboxes(container) {
    return this.instance.getConditionsCheckboxes(container);
  }

  getHostedFieldsForm() {
    return this.instance.getHostedFieldsForm();
  }

  getNotificationConditions() {
    return this.instance.getNotificationConditions();
  }

  getNotificationPaymentCanceled() {
    return this.instance.getNotificationPaymentCanceled();
  }

  getNotificationPaymentContainer() {
    return this.instance.getNotificationPaymentContainer();
  }

  getNotificationPaymentContainerTarget() {
    return this.instance.getNotificationPaymentContainerTarget();
  }

  getNotificationPaymentError() {
    return this.instance.getNotificationPaymentError();
  }

  getNotificationPaymentErrorText() {
    return this.instance.getNotificationPaymentErrorText();
  }

  getPaymentOption(container) {
    return this.instance.getPaymentOption(container);
  }

  getPaymentOptionLabel(container, text) {
    return this.instance.getPaymentOptionLabel(container, text);
  }

  getPaymentOptionLabelLegacy(container, id) {
    return this.instance.getPaymentOptionLabelLegacy(container, id);
  }

  getPaymentOptionSelect(container) {
    return this.instance.getPaymentOptionSelect(container);
  }

  getPaymentOptionContainer(id) {
    return this.instance.getPaymentOptionContainer(id);
  }

  getPaymentOptionAdditionalInformation(id) {
    return this.instance.getPaymentOptionAdditionalInformation(id);
  }

  getPaymentOptionFormContainer(id) {
    return this.instance.getPaymentOptionFormContainer(id);
  }

  getPaymentOptionFormButton(container, id) {
    return this.instance.getPaymentOptionFormButton(container, id);
  }

  getPaymentOptionsContainer() {
    return this.instance.getPaymentOptionsContainer();
  }

  getPaymentOptions() {
    return this.instance.getPaymentOptions();
  }
}
