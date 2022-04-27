{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}
{if isset($totalCartPrice) and $payIn4XisOrderPageEnabled == true}
  {if not isset($content_only) or $content_only === 0}
    <div id="ps-checkout-pp-message-container"
      style='display: none;'
      data-pp-message-ps_checkout
      data-pp-placement="cart"
      data-pp-style-layout="text"
      data-pp-style-logo-type="inline"
      data-pp-style-text-color="black"
      data-pp-amount="{$totalCartPrice}"
    ></div>
    <script>
      {literal}
      window.ps_checkout = window.ps_checkout || {
        events: new EventTarget(),
      };

      if (!window.ps_checkout.pay_in_4x_listener) {
        window.ps_checkout.events.addEventListener('init', window.ps_checkout.pay_in_4x_listener = function() {
          var ppMessages = Array.prototype.slice.call(document.querySelectorAll('[data-pp-message-ps_checkout]'));
          if (ppMessages.length > 0) {
            var lastMessage = ppMessages[ppMessages.length - 1];
            lastMessage.removeAttribute('style');
          }

          var updateCartSummary = window['updateCartSummary'];
          window['updateCartSummary'] = function(...args) {
            var cart = args[0];
            var totalPrice = (cart || {}).total_price || 0.0;

            lastMessage.setAttribute('data-pp-amount', totalPrice);
            return updateCartSummary.apply(updateCartSummary, arguments);
          }
        });
      }

      window.onload = function () {
        if (
          window.ps_checkoutPayPalSdkInstance
          && window.ps_checkoutPayPalSdkInstance.Messages
          && window.ps_checkoutPayPalSdkInstance.Marks({ fundingSource: 'paylater' }).isEligible()
        ) {
          document.getElementById('ps-checkout-pp-message-container').setAttribute('data-pp-message', true);
        }
      }
      {/literal}
    </script>
  {/if}
{/if}
