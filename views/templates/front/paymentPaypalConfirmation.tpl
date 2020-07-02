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

<link rel="preload" href="{$paypalSdkLink|escape:'javascript':'UTF-8'|replace:'&amp;':'&'}" as="script">

{capture name=path}
<a href="{$link->getPageLink('order', true, NULL, 'step=3')|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='ps_checkout'}">
  {l s='Checkout' mod='ps_checkout'}
</a>
<span class="navigation-pipe">
  {$navigationPipe}
</span>
{l s='Card payment' mod='ps_checkout'}
{/capture}

<h1 class="page-heading">
  {l s='Order summary' mod='ps_checkout'}
</h1>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
<p class="alert alert-warning">
  {l s='Your shopping cart is empty.' mod='ps_checkout'}
</p>
{else}
<form method="post">
  <div class="box">
    <h3 class="page-subheading">
      {l s='PayPal' mod='ps_checkout'}
    </h3>
    <p>
      <strong class="dark">
        {l s='You have chosen to pay by PayPal.' mod='ps_checkout'} {l s='Here is a short summary of your order:' mod='ps_checkout'}
      </strong>
    </p>
    <p>
      - {l s='The total amount of your order comes to' mod='ps_checkout'}
      <b><span id="amount" class="price">{displayPrice price=$total}</span></b>
      {if $use_taxes == 1}
      {l s='(tax incl.)' mod='ps_checkout'}
      {/if}
    </p>
    <p>
      - {l s='Please confirm your order by clicking on the method you want to pay with.' mod='ps_checkout'}
    </p>
    <div id="paypal-button-container" style="width: 300px; margin-top: 20px;"></div>
  </div>

  <div class="cart_navigation clearfix" id="cart_navigation">
    <div class="flex-display">
      <a class="button-exclusive btn btn-default" href="{$link->getPageLink('order', true, NULL, 'step=3')|escape:'html':'UTF-8'}">
        <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='ps_checkout'}
      </a>
    </div>
  </div>
</form>
{/if}

<script>
  const paypalOrderId = "{$paypalOrderId|escape:'javascript':'UTF-8'}";
  const validateOrderLinkByPaypal = "{$validateOrderLinkByPaypal|escape:'javascript':'UTF-8'|replace:'&amp;':'&' nofilter}";
  /**
   * Create paypal script
   */
  function initPaypalScript() {
    if (typeof paypalSdkPsCheckout !== 'undefined') {
      return;
    }

    let psCheckoutScript = document.getElementById('paypalSdkPsCheckout');

    if (null !== psCheckoutScript) {
      return;
    }

    const paypalScript = document.createElement('script');
    paypalScript.setAttribute('src', "{$paypalSdkLink|escape:'javascript':'UTF-8'|replace:'&amp;':'&' nofilter}");
    paypalScript.setAttribute('data-client-token', "{$clientToken|escape:'javascript':'UTF-8'}");
    paypalScript.setAttribute('id', 'psCheckoutPaypalSdk');
    paypalScript.setAttribute('data-namespace', 'paypalSdkPsCheckout');
    paypalScript.setAttribute('data-enable-3ds', '');
    paypalScript.setAttribute('async', '');
    document.head.appendChild(paypalScript);
  }

  initPaypalScript();
</script>
