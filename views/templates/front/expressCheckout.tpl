{**
 * 2007-2019 PrestaShop and Contributors
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
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

<script type='text/javascript' src='{$jsExpressCheckoutPath|escape:'javascript':'UTF-8'}'></script>

<link rel="preload" href="https://www.paypal.com/sdk/js?components=buttons&client-id={$paypalClientId|escape:'htmlall':'UTF-8'}&merchant-id={$merchantId|escape:'htmlall':'UTF-8'}&intent={$intent|escape:'htmlall':'UTF-8'}&currency={$currencyIsoCode|escape:'htmlall':'UTF-8'}&commit=false&disable-funding=credit,card" as="script">

<div id="pscheckout-express-checkout" style="display:none;">
  {if $displayMode eq 'cart'}
  <div class="cart">{l s='or' mod='ps_checkout'}</div>
  {/if}

  {if $displayMode eq 'checkout'}
  <div class="checkout">
    <b>{l s='Fast checkout' mod='ps_checkout'}</b>
  </div>
  {/if}

  <div id="paypal-button-container" class="" style="max-width:300px;"></div>
</div>

<style>
#pscheckout-express-checkout .cart {
  margin-top:15px;
  margin-bottom:15px
}
#pscheckout-express-checkout .checkout {
  margin-top:15px;
  margin-bottom:15px
}
</style>

<script>
/**
 * Load paypal script
 */
function loadPaypalScript() {
  if (typeof paypalSdkPsCheckout !== 'undefined') {
    return;
  }

  let psCheckoutScript = document.getElementById('psCheckoutPaypalSdk');

  if (psCheckoutScript !== null) {
    return;
  }

  const paypalScript = document.createElement('script');
  paypalScript.setAttribute('src', "https://www.paypal.com/sdk/js?components=buttons&client-id={$paypalClientId|escape:'htmlall':'UTF-8'}&merchant-id={$merchantId|escape:'htmlall':'UTF-8'}&intent={$intent|escape:'htmlall':'UTF-8'}&currency={$currencyIsoCode|escape:'htmlall':'UTF-8'}&commit=false&disable-funding=credit,card");
  paypalScript.setAttribute('id', 'psCheckoutPaypalSdk');
  paypalScript.setAttribute('data-namespace', 'paypalSdkPsCheckoutEC');
  paypalScript.setAttribute('async', '');
  document.head.appendChild(paypalScript);
}

loadPaypalScript();
</script>

{literal}
<script type="text/javascript">
  var checkoutLink = "{/literal}{$checkoutLink|escape:'javascript':'UTF-8'}{literal}";
  var displayMode = "{/literal}{$displayMode|escape:'javascript':'UTF-8'}{literal}";
  var isPs176 = "{/literal}{$isPs176|escape:'javascript':'UTF-8'}{literal}";
  var expressCheckoutController = "{/literal}{$expressCheckoutController|escape:'javascript':'UTF-8'}{literal}";
  var paypalIsActive = "{/literal}{$paypalIsActive|escape:'javascript':'UTF-8'}{literal}";
</script>
{/literal}
