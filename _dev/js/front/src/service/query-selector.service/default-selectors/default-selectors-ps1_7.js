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
export const DefaultSelectors1_7 = {
  BASE_PAYMENT_CONFIRMATION: '#payment-confirmation [type="submit"]',

  CONDITIONS_CHECKBOXES: '#conditions-to-approve input[type="checkbox"]',

  LOADER_PARENT: 'body',

  NOTIFICATION_CONDITIONS: '.accept-cgv',
  NOTIFICATION_PAYMENT_CANCELLED: '#ps_checkout-canceled',
  NOTIFICATION_PAYMENT_ERROR: '#ps_checkout-error',
  NOTIFICATION_PAYMENT_ERROR_TEXT: '#ps_checkout-error-text',

  PAYMENT_OPTIONS: '.payment-options',
  PAYMENT_OPTIONS_LOADER: '#ps_checkout-loader',
  PAYMENT_OPTION_RADIOS:
    '.payment-options input[type="radio"][name="payment-option"]',

  EXPRESS_CHECKOUT_CONTAINER_PRODUCT_PAGE:
    '#product .product-add-to-cart .product-quantity',
  EXPRESS_CHECKOUT_CONTAINER_CART_PAGE:
    '#cart .cart-summary .cart-detailed-actions',
  EXPRESS_CHECKOUT_CONTAINER_CHECKOUT_PAGE:
    '#checkout-personal-information-step .content',

  PAY_LATER_OFFER_MESSAGE_CONTAINER_PRODUCT: '.product-prices',
  PAY_LATER_OFFER_MESSAGE_CONTAINER_CART_SUMMARY: '.cart-summary-totals',

  PAY_LATER_BANNER_CONTAINER: '#notifications .container',

  CARD_FIELDS: {
    FORM: '#ps_checkout-card-fields-form',
    NAME: '#ps_checkout-card-fields-name',
    NUMBER: '#ps_checkout-card-fields-number',
    EXPIRY: '#ps_checkout-card-fields-expiry',
    CVV: '#ps_checkout-card-fields-cvv',
    NAME_ERROR: '#ps_checkout-card-fields-name-error',
    NUMBER_ERROR: '#ps_checkout-card-fields-number-error',
    VENDOR_ERROR: '#ps_checkout-card-fields-vendor-error',
    EXPIRY_ERROR: '#ps_checkout-card-fields-expiry-error',
    CVV_ERROR: '#ps_checkout-card-fields-cvv-error',
  },

  PAYMENT_METHOD_LOGO_PRODUCT_CONTAINER: '#product .product-add-to-cart',
  PAYMENT_METHOD_LOGO_CART_CONTAINER: '#cart .cart-summary .cart-detailed-actions'
};
