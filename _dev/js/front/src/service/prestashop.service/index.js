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

import { PrestashopPs1_6Service } from './prestashop-ps1_6.service';
import { PrestashopPs1_7Service } from './prestashop-ps1_7.service';

export class PrestashopService {
  constructor() {
    this.instance = {
      [PS_VERSION_1_6]: PrestashopPs1_6Service,
      [PS_VERSION_1_7]: PrestashopPs1_7Service
    }[this.getVersion()];
  }

  getProductDetails() {
    return this.instance.getProductDetails();
  }

  isCartPage() {
    return !!this.instance.isCartPage();
  }

  isOrderPersonalInformationStepPage() {
    return !!this.instance.isOrderPersonalInformationStepPage();
  }

  isOrderPaymentStepPage() {
    return !!this.instance.isOrderPaymentStepPage();
  }

  isOrderPage() {
    return this.instance.isOrderPage();
  }

  isNativeOnePageCheckoutPage() {
    return this.instance.isNativeOnePageCheckoutPage();
  }

  isIframeProductPage() {
    return !!this.instance.isIframeProductPage();
  }

  isProductPage() {
    return !!this.instance.isProductPage();
  }

  isLogged() {
    return this.instance.isLogged();
  }

  isGuestCheckoutEnabled() {
    return this.instance.isGuestCheckoutEnabled();
  }

  hasProductInCart() {
    return this.instance.hasProductInCart();
  }

  getVersion() {
    if (!window.prestashop) {
      return PS_VERSION_1_6;
    }

    return PS_VERSION_1_7;
  }

  onUpdatedCart(listener) {
    this.instance.onUpdatedCart(listener);
  }

  onUpdatePaymentMethods(listener) {
    this.instance.onUpdatePaymentMethods(listener);
  }

  onUpdatedShoppingCartExtra(listener) {
    this.instance.onUpdatedShoppingCartExtra(listener);
  }
}
