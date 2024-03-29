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
export const DefaultSelectors1_6 = {
  BASE_PAYMENT_CONFIRMATION: '#ps_checkout-express-checkout-submit-button',

  CONDITIONS_CHECKBOXES: 'input[name="cgv"]',

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
    'body.product .box-cart-bottom .buttons_bottom_block',
  EXPRESS_CHECKOUT_CONTAINER_CART_PAGE: 'body.order .cart_navigation_extra',
  EXPRESS_CHECKOUT_CONTAINER_CHECKOUT_PAGE:
    'body.authentication #create-account_form, body.order-opc #opc_account_choice:not([style*="display: none"]) .opc-button, body.order-opc #opc_account_form:not([style*="display: none"])',

  PAY_LATER_OFFER_MESSAGE_CONTAINER_PRODUCT: '.content_prices',
  PAY_LATER_OFFER_MESSAGE_CONTAINER_CART_SUMMARY: '#total_price_container',

  PAY_LATER_BANNER_CONTAINER: '.header-container',

  CARD_FIELDS: {
    FORM: '#ps_checkout-card-fields-form',
    NAME: '#ps_checkout-card-fields-card-name',
    NUMBER: '#ps_checkout-card-fields-card-number',
    EXPIRY: '#ps_checkout-card-fields-card-expiry',
    CVV: '#ps_checkout-card-fields-card-cvv',
    NAME_ERROR: '#ps_checkout-card-fields-card-name-error',
    NUMBER_ERROR: '#ps_checkout-card-fields-card-number-error',
    VENDOR_ERROR: '#ps_checkout-card-fields-card-vendor-error',
    EXPIRY_ERROR: '#ps_checkout-card-fields-card-expiry-error',
    CVV_ERROR: '#ps_checkout-card-fields-card-cvv-error',
  },

  PAYMENT_METHOD_LOGO_PRODUCT_CONTAINER: 'body.product .box-cart-bottom .buttons_bottom_block',
  PAYMENT_METHOD_LOGO_CART_CONTAINER: 'body.order .cart_navigation_extra'
};
