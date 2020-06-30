{**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{if $paymentError}
  <div class="alert alert-danger">
    <p><strong>{l s='Processing payment error' mod='ps_checkout'}</strong></p>
    <p>{l s='Payment failed, you have not been charged.' mod='ps_checkout'}</p>
    <p>{$paymentErrorMessage|escape:'htmlall':'UTF-8'}</p>
  </div>
{/if}

{if $isExpressCheckout}
<div class="express-checkout-block mb-2">
  <img src="{$paypalLogoPath|escape:'htmlall':'UTF-8'}" class="express-checkout-img" alt="PayPal">
  <p class="express-checkout-label">
    {$translatedText|escape:'htmlall':'UTF-8'}
  </p>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const paymentOptions = document.querySelectorAll('input[name="payment-option"]');

    paymentOptions.forEach(function(paymentOption) {
      const paymentOptionContainer = document.getElementById(paymentOption.id + '-container');
      const paymentOptionName = paymentOption.getAttribute('data-module-name');

      if ('ps_checkout_expressCheckout' === paymentOptionName) {
        paymentOption.click();
      } else {
        paymentOption.disabled = true;
        paymentOptionContainer.style.display = 'none';
      }
    });
  });
</script>
{/if}
