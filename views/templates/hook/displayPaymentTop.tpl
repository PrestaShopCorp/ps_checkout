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

<div id="ps_checkout-notification-container">
  <p id="ps_checkout-canceled" class="alert alert-info" style="display:none;">{l s='Processing payment canceled, please choose another payment method or try again.' mod='ps_checkout'}</p>
  <div id="ps_checkout-error" class="alert alert-danger" style="display:none;">
    <p><strong>{l s='Processing payment error' mod='ps_checkout'}</strong></p>
    <div id="ps_checkout-error-text"></div>
  </div>
</div>

{if $is17 && $isExpressCheckout}
<div class="express-checkout-block mb-2">
  <img src="{$paypalLogoPath|escape:'htmlall':'UTF-8'}" class="express-checkout-img" alt="PayPal">
  <p class="express-checkout-label">
    {$translatedText|escape:'htmlall':'UTF-8'}
  </p>
</div>
{else}
  {if $is17 || $isOnePageCheckout16}
  <div id="ps_checkout-loader" class="express-checkout-block mb-2">
    <div class="express-checkout-block-wrapper">
      <p class="express-checkout-spinner-text">
        {$translatedText|escape:'htmlall':'UTF-8'}
      </p>
      <div class="express-checkout-spinner">
        <img src="{$spinnerPath|escape:'htmlall':'UTF-8'}" alt="{$translatedText|escape:'htmlall':'UTF-8'}">
      </div>
    </div>
  </div>
  {/if}
{/if}

{if $is17}
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const paymentOptions = document.querySelectorAll('input[name="payment-option"]');

    if (null !== paymentOptions) {
      paymentOptions.forEach(function(paymentOption) {
        const paymentOptionContainer = document.getElementById(paymentOption.id + '-container');
        const paymentOptionName = paymentOption.getAttribute('data-module-name');

        if (-1 !== paymentOptionName.search('ps_checkout')) {
          paymentOptionContainer.style.display = 'none';
        }
      });
    }
  });
</script>
{/if}
