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
/* istanbul ignore file */
// TODO: Remove this
export const HtmlSelectorsPs1_7Constants = {
  ANY_PAYMENT_OPTION: '[data-module-name="ps_checkout"]',

  BUTTONS_CONTAINER_ID: 'ps_checkout-buttons-container',

  CHECKOUT_EXPRESS_CART_BUTTON_CONTAINER_ID:
    'js-ps_checkout-express-button-container',
  CHECKOUT_EXPRESS_CHECKOUT_BUTTON_CONTAINER:
    '#checkout-personal-information-step .content',
  CHECKOUT_EXPRESS_PRODUCT_BUTTON_CONTAINER: '.product-add-to-cart',

  CONDITIONS_CHECKBOX_CONTAINER_ID: 'conditions-to-approve',
  CONDITION_CHECKBOX: 'input[type="checkbox"]',

  HOSTED_FIELDS_FORM_ID: 'ps_checkout-hosted-fields-form',

  NOTIFICATION_CONDITIONS: '.accept-cgv',
  NOTIFICATION_PAYMENT_CANCELED_ID: 'ps_checkout-canceled',
  NOTIFICATION_PAYMENT_ERROR_ID: 'ps_checkout-error',
  NOTIFICATION_PAYMENT_ERROR_TEXT_ID: 'ps_checkout-error-text',

  PAYMENT_OPTION: '[name="payment-option"]',
  PAYMENT_OPTION_LABEL: id => `label[for="${id}"]`,
  PAYMENT_OPTION_SELECT: '[name="select_payment_option"]',
  PAYMENT_OPTION_CONTAINER_ID: id => `${id}-container`,
  PAYMENT_OPTION_ADDITIONAL_INFORMATION_ID: id =>
    `${id}-additional-information`,
  PAYMENT_OPTION_FORM_CONTAINER_ID: id => `pay-with-${id}-form`,
  PAYMENT_OPTION_FORM_BUTTON: id => `#pay-with-${id}`,

  PAYMENT_OPTIONS_CONTAINER: '.payment-options'
};
