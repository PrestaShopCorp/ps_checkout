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
    const id_product = document.getElementById('product_page_product_id');
    const id_product_attribute = document.getElementById('idCombination');
    const id_customization = window.customizationId;
    const quantity_wanted = document.getElementById('quantity_wanted');

    return {
      id_product: id_product.value || '',
      id_product_attribute: id_product_attribute.value || '',
      id_customization: id_customization || '',
      quantity_wanted: quantity_wanted.value || ''
    };
  }

  static isCartPage() {
    if (document.body.id === 'order') {
      return document.querySelector('.step_current.first');
    }

    return false;
  }

  static isOrderPaymentStepPage() {
    if (document.body.id === 'order') {
      return document.getElementById('ps_checkout-displayPayment');
    }

    return document.body.id === 'order-opc';
  }

  static isOrderPage() {
    return document.body.id === 'order' || document.body.id === 'order-opc';
  }

  static isNativeOnePageCheckoutPage() {
    return document.body.id === 'order-opc';
  }

  static isOrderPersonalInformationStepPage() {
    return (
      document.body.id === 'authentication' ||
      (document.body.id === 'order-opc' && !window.isLogged && !window.isGuest)
    );
  }

  static isIframeProductPage() {
    return new URL(window.location).searchParams.get('content_only') === '1';
  }

  static isProductPage() {
    return document.body.id === 'product';
  }

  static isLogged() {
    return !!window.isLogged || !!window.isGuest;
  }

  static isGuestCheckoutEnabled() {
    return !!window.guestCheckoutEnabled;
  }

  static hasProductInCart() {
    return !!window.ps_checkoutCartProductCount;
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

  static onUpdatedShoppingCartExtra(listener) {
    if (window['updateHookShoppingCartExtra']) {
      const updateHookShoppingCartExtra = window['updateHookShoppingCartExtra'];
      window['updateHookShoppingCartExtra'] = (...args) => {
        updateHookShoppingCartExtra(...args);
        listener(...args);
      };
    }
  }
}
