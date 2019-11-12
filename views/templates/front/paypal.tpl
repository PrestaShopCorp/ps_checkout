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

<link rel="preload" href="https://www.paypal.com/sdk/js?components=hosted-fields,buttons&amp;client-id={$paypalClientId|escape:'htmlall':'UTF-8'}&amp;merchant-id={$merchantId|escape:'htmlall':'UTF-8'}&amp;intent={$intent|escape:'htmlall':'UTF-8'}&amp;currency={$currencyIsoCode|escape:'htmlall':'UTF-8'}" as="script">

<div class="paypal-tips">{l s='You will be redirected to the related gateway to complete payment' mod='ps_checkout'}</div>

<div>
  <div id="paypal-button-container"></div>

  <form id="conditions-to-approve-paypal" method="GET">
    <label for="conditions_to_approve">
      <input id="conditions_to_approve" type="checkbox" name="conditions_to_approve" class="buttons-approve">
      {assign var="link_url" value=$link->getCMSLink('3')}
      {l s='I agree to the [1]terms of service[/1] and will adhere to them unconditionally.' mod='ps_checkout' tags=["<a href=\"$link_url\" id=\"cta-terms-and-conditions-checkout\">"]}
    </label>
  </form>
</div>

<div id="paypal-approve-error" class="hide-paypal-error">
  <article class="alert alert-danger" role="alert" data-alert="danger">
    <ul>
      <li>{l s='Please indicate that you have read Terms & Conditions and accept all terms.' mod='ps_checkout'}</li>
    </ul>
  </article>
</div>

<script>
/**
 * Create paypal script
 */
function initPaypalScript() {
  if (typeof paypal !== 'undefined') {
    return;
  }

  let psCheckoutScript = document.getElementById('paypalSdkPsCheckout');

  if (psCheckoutScript !== null) {
    return;
  }

  const paypalScript = document.createElement('script');
  paypalScript.setAttribute('src', "https://www.paypal.com/sdk/js?components=hosted-fields,buttons&client-id={$paypalClientId|escape:'htmlall':'UTF-8'}&merchant-id={$merchantId|escape:'htmlall':'UTF-8'}&intent={$intent|escape:'htmlall':'UTF-8'}&currency={$currencyIsoCode|escape:'htmlall':'UTF-8'}");
  paypalScript.setAttribute('data-client-token', "{$clientToken|escape:'htmlall':'UTF-8'}");
  paypalScript.setAttribute('id', 'paypalSdkPsCheckout');
  paypalScript.setAttribute('async', '');
  document.head.appendChild(paypalScript);
}

initPaypalScript();
</script>

{literal}
<script type="text/javascript">
  const cardNumberPlaceholder = "{/literal}{l s='Card number' mod='ps_checkout'}{literal}";
  const expDatePlaceholder = "{/literal}{l s='MM/YY' mod='ps_checkout'}{literal}";
  const cvvPlaceholder = "{/literal}{l s='XXX' mod='ps_checkout'}{literal}";
  const paypalOrderId = "{/literal}{$paypalOrderId|escape:'javascript':'UTF-8'}{literal}";
  const validateOrderLinkByCard = "{/literal}{$validateOrderLinkByCard|escape:'javascript':'UTF-8'}{literal}";
  const validateOrderLinkByPaypal = "{/literal}{$validateOrderLinkByPaypal|escape:'javascript':'UTF-8'}{literal}";
  const cardIsActive = "{/literal}{$cardIsActive|escape:'javascript':'UTF-8'}{literal}";
  const paypalIsActive = "{/literal}{$paypalIsActive|escape:'javascript':'UTF-8'}{literal}";
</script>
{/literal}
