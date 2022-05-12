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
function getTranslations() {
  return {
    ...Object.keys(window.ps_checkoutPayWithTranslations || {}).reduce(
      (result, name) => {
        result[`funding-source.name.${name}`] =
          window.ps_checkoutPayWithTranslations[name];
        return result;
      },
      {}
    ),
    ...window.ps_checkoutCheckoutTranslations
  };
}

export const PsCheckoutConfig = {
  createUrl: window.ps_checkoutCreateUrl,
  checkCartUrl: window.ps_checkoutCheckUrl,
  validateOrderUrl: window.ps_checkoutValidateUrl,
  confirmationUrl: window.ps_checkoutConfirmUrl,
  cancelUrl: window.ps_checkoutCancelUrl,
  getTokenUrl: window.ps_checkoutGetTokenURL,
  checkoutCheckoutUrl: window.ps_checkoutCheckoutUrl,
  expressCheckoutUrl: window.ps_checkoutExpressCheckoutUrl,

  hostedFieldsEnabled: window.ps_checkoutHostedFieldsEnabled,

  translations: getTranslations(),

  loaderImage: window.ps_checkoutLoaderImage,
  customMark: {
    card: window.ps_checkoutCardFundingSourceImg
  },

  autoRenderDisabled: window.ps_checkoutAutoRenderDisabled,
  expressCheckout: {
    active: window.ps_checkoutExpressCheckoutSelected,
    enabled: {
      cart: window.ps_checkoutExpressCheckoutCartEnabled,
      order: window.ps_checkoutExpressCheckoutOrderEnabled,
      product: window.ps_checkoutExpressCheckoutProductEnabled
    }
  },

  payLater: {
    message: {
      order: window.ps_checkoutPayLaterOrderPageMessageEnabled,
      product: window.ps_checkoutPayLaterProductPageMessageEnabled
    },
    banner: {
      home: window.ps_checkoutPayLaterHomePageBannerEnabled,
      category: window.ps_checkoutPayLaterCategoryPageBannerEnabled,
      order: window.ps_checkoutPayLaterOrderPageBannerEnabled,
      product: window.ps_checkoutPayLaterProductPageBannerEnabled
    },
    button: {
      cart: window.ps_checkoutPayLaterCartPageButtonEnabled,
      order: window.ps_checkoutPayLaterOrderPageButtonEnabled,
      product: window.ps_checkoutPayLaterProductPageButtonEnabled
    }
  },

  fundingSourcesSorted: window.ps_checkoutFundingSourcesSorted,

  orderId: window.ps_checkoutPayPalOrderId
};
