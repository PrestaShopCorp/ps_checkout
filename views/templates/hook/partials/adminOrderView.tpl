{**
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
 *}

<script>
  if (undefined !== ps_checkout) {
    ps_checkout.initialize({
      legacy: {$legacy|intval},
      orderPrestaShopId: {$orderPrestaShopId|intval},
      orderPayPalBaseUrl: '{$orderPayPalBaseUrl|escape:'javascript'}',
      orderPayPalContainer: '.paypal-order-container',
      orderPayPalLoaderContainer: '.paypal-order-loader',
      orderPayPalNotificationsContainer: '.paypal-order-notifications',
      orderPayPalRefundButton: '#ps_checkout button.refund',
      orderPayPalModalContainerPrefix: '#ps-checkout-refund-',
      orderPayPalModalContainer: '.ps-checkout-order',
      orderPayPalModalNotificationsContainer: '.modal-notifications',
      orderPayPalModalContentContainer: '.modal-content-container',
      orderPayPalModalLoaderContainer: '.modal-loader',
      orderPayPalModalRefundForm: '.ps-checkout-refund-form',
      orderPayPalCaptureButton: '#ps_checkout button.capture',
      orderPayPalModalCaptureContainerPrefix: '#ps-checkout-capture-',
      orderPayPalModalCaptureForm: '.ps-checkout-capture-form',
      orderPayPalModalCaptureContainer: '.ps-checkout-order',
      orderPayPalVoidButton: '#ps_checkout button.void',
      orderPayPalModalVoidContainerPrefix: '#ps-checkout-void-',
      orderPayPalModalVoidForm: '.ps-checkout-void-form',
    });
  }
</script>
