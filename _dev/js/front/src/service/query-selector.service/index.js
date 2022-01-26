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

import { QuerySelectorPs1_6Service } from './query-selector-ps1_6.service';
import { QuerySelectorPs1_7Service } from './query-selector-ps1_7.service';

export class QuerySelectorService extends BaseClass {
  static Inject = {
    prestashopService: 'PrestashopService'
  };

  constructor(app) {
    super(app);

    this.instance = {
      [PS_VERSION_1_6]: QuerySelectorPs1_6Service,
      [PS_VERSION_1_7]: QuerySelectorPs1_7Service
    }[this.prestashopService.getVersion()];
  }

  getBasePaymentConfirmation() {
    return this.instance.getBasePaymentConfirmation();
  }

  getConditionsCheckboxes() {
    return this.instance.getConditionsCheckboxes();
  }

  getLoaderParent() {
    return this.instance.getLoaderParent();
  }

  getNotificationConditions() {
    return this.instance.getNotificationConditions();
  }

  getNotificationPaymentCanceled() {
    return this.instance.getNotificationPaymentCanceled();
  }

  getNotificationPaymentError() {
    return this.instance.getNotificationPaymentError();
  }

  getNotificationPaymentErrorText() {
    return this.instance.getNotificationPaymentErrorText();
  }

  getPaymentOptions() {
    return this.instance.getPaymentOptions();
  }

  getPaymentOptionsLoader() {
    return this.instance.getPaymentOptionsLoader();
  }

  getPaymentOptionRadios() {
    return this.instance.getPaymentOptionRadios();
  }

  getCheckoutExpressCheckoutButtonContainerCart() {
    return this.instance.getCheckoutExpressCheckoutButtonContainerCart();
  }

  getCheckoutExpressCheckoutButtonContainerCheckout() {
    return this.instance.getCheckoutExpressCheckoutButtonContainerCheckout();
  }

  getCheckoutExpressCheckoutButtonContainerProduct() {
    return this.instance.getCheckoutExpressCheckoutButtonContainerProduct();
  }
}
