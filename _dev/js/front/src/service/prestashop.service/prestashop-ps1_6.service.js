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
export class PrestashopPs1_6Service {
  static getProductDetails() {
    return {};
  }

  static isCartPage() {
    return false;
  }

  static isOrderPaymentStepPage() {
    if (document.body.id === 'order') {
      return document.getElementById('ps_checkout-displayPayment');
    }

    return document.body.id === 'order-opc';
  }

  static isOrderPersonalInformationStepPage() {
    return false;
  }

  static isProductPage() {
    return false;
  }

  static onUpdatedCart() {}

  static onUpdatePaymentMethods(listener) {
    if (window['updatePaymentMethods']) {
      const updatePaymentMethods = window['updatePaymentMethods'];
      window['updatePaymentMethods'] = (...args) => {
        updatePaymentMethods(...args);
        listener(...args);
      };
    }
  }
}
