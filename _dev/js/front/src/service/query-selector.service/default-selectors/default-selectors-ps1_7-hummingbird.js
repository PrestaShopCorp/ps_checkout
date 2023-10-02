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
export const DefaultSelectors1_7Hummingbird = {
  BASE_PAYMENT_CONFIRMATION: '#payment-confirmation [type="submit"]',

  CONDITIONS_CHECKBOXES: '#conditions-to-approve input[type="checkbox"]',

  LOADER_PARENT: 'body',

  NOTIFICATION_CONDITIONS: '.accept-cgv',
  NOTIFICATION_PAYMENT_CANCELLED: '#ps_checkout-canceled',
  NOTIFICATION_PAYMENT_ERROR: '#ps_checkout-error',
  NOTIFICATION_PAYMENT_ERROR_TEXT: '#ps_checkout-error-text',

  PAYMENT_OPTIONS: '.payment__list',
  PAYMENT_OPTIONS_LOADER: '#ps_checkout-loader',
  PAYMENT_OPTION_RADIOS:
    '.payment__list input[type="radio"][name="payment-option"]',

  EXPRESS_CHECKOUT_CONTAINER_PRODUCT_PAGE:
    '#product .product__add-to-cart .product__minimal-quantity',
  EXPRESS_CHECKOUT_CONTAINER_CART_PAGE:
    '#cart .cart-summary .cart-detailed__actions',
  EXPRESS_CHECKOUT_CONTAINER_CHECKOUT_PAGE:
    '#checkout-personal-information-step .step__content',

  PAY_LATER_OFFER_MESSAGE_CONTAINER_PRODUCT: '.product__prices',
  PAY_LATER_OFFER_MESSAGE_CONTAINER_CART_SUMMARY: '.cart-summary__totals',

  PAY_LATER_BANNER_CONTAINER: '#notifications .container'
};
