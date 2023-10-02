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
export class PrestashopPs1_7Service {
  static getProductDetails() {
    const productDetails = document.getElementById('product-details');

    if (!productDetails || !productDetails.dataset || !productDetails.dataset.product) {
      throw new Error('Unable to retrieve product details from DOM: document.getElementById("product-details").dataset.product');
    }

    return JSON.parse(
      productDetails.dataset.product
    );
  }

  static isHomePage() {
    return document.body.id === 'index';
  }

  static isCategoryPage() {
    return document.body.id === 'category';
  }

  static isCartPage() {
    return document.body.id === 'cart';
  }

  static isOrderPaymentStepPage() {
    if (document.body.id !== 'checkout') return false;
    return document.querySelector('[data-module-name^="ps_checkout"]');
  }

  static isOrderPage() {
    return document.body.id === 'checkout';
  }

  static isNativeOnePageCheckoutPage() {
    return false; // This doesn't exist in PrestaShop 1.7
  }

  static isOrderPersonalInformationStepPage() {
    if (document.body.id !== 'checkout') return false;
    const step = document.querySelector('#checkout-personal-information-step');

    return step && (step.classList.contains('-current') || step.classList.contains('step--current'));
  }

  static isIframeProductPage() {
    return false;
  }

  static isProductPage() {
    return document.body.id === 'product';
  }

  static isLogged() {
    return window.prestashop?.customer?.is_logged || false;
  }

  static isGuestCheckoutEnabled() {
    return !!document.querySelector('#checkout-guest-form');
  }

  static hasProductInCart() {
    return !!window.ps_checkoutCartProductCount;
  }

  static getCartAmount() {
    return window.prestashop?.cart?.totals?.total?.amount || '';
  }

  static getProductPrice() {
    let productPrice = document.querySelector('.current-price .current-price-value');

    if (!productPrice) {
      productPrice = document.querySelector('.current-price [itemprop="price"]');
    }

    if (productPrice) {
      return productPrice.getAttribute('content');
    }

    return '';
  }

  static isAddToCartButtonDisabled() {
    let addToCartElement = document.querySelector('.page-product:not(.modal-open) .row .product-add-to-cart, .page-product:not(.modal-open) .product-container .product-add-to-cart, .page-product:not(.modal-open) .row .js-product-add-to-cart, .page-product:not(.modal-open) .product-container .js-product-add-to-cart');
    let addToCartButtonElement = addToCartElement.querySelector('button.add-to-cart');

    return addToCartButtonElement ? addToCartButtonElement.disabled : true;
  }

  static onUpdatedCart(listener) {
    if (window['prestashop'] && window['prestashop'].on) {
      window['prestashop'].on('updatedCart', listener);
    } else {
      console.error('');
    }
  }

  static onUpdatedProduct(listener) {
    if (window['prestashop'] && window['prestashop'].on) {
      window['prestashop'].on('updatedProduct', listener);
    } else {
      console.error('');
    }
  }

  static onUpdatePaymentMethods() {}

  static onUpdatedShoppingCartExtra() {}
}
