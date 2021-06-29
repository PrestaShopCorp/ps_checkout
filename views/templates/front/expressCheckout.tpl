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

<script type="text/javascript" src="{$jsExpressCheckoutPath|escape:'javascript':'UTF-8'|replace:'&amp;':'&'}"></script>

<link rel="preload" href="{$paypalSdkLink|escape:'javascript':'UTF-8'|replace:'&amp;':'&'}" as="script">

<div id="pscheckout-express-checkout" style="display:none;">
  {if $displayMode eq 'cart'}
  <div class="cart">{l s='or' mod='ps_checkout'}</div>
  {/if}

  {if $displayMode eq 'checkout'}
  <div class="checkout">
    <b>{l s='Fast checkout' mod='ps_checkout'}</b>
  </div>
  {/if}

  <div id="paypal-button-container"></div>
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
  #paypal-button-container {
    max-width:300px;
  }
  .page-cart #paypal-button-container {
    margin: auto;
  }
</style>

<script>
  const checkoutLink = "{$checkoutLink|escape:'javascript':'UTF-8'|replace:'&amp;':'&' nofilter}";
  const displayMode = "{$displayMode|escape:'javascript':'UTF-8'}";
  const isPs176 = "{$isPs176|escape:'javascript':'UTF-8'}";
  const expressCheckoutController = "{$expressCheckoutController|escape:'javascript':'UTF-8'|replace:'&amp;':'&' nofilter}";
  const paypalIsActive = "{$paypalIsActive|escape:'javascript':'UTF-8'}";
  /**
   * Load paypal script
   */
  function loadPaypalScript() {
    if (typeof paypalSdkPsCheckout !== 'undefined') {
      return;
    }

    let psCheckoutScript = document.getElementById('psCheckoutPaypalSdk');

    if (null !== psCheckoutScript) {
      return;
    }

    const paypalScript = document.createElement('script');
    paypalScript.setAttribute('src', "{$paypalSdkLink|escape:'javascript':'UTF-8'|replace:'&amp;':'&' nofilter}");
    paypalScript.setAttribute('id', 'psCheckoutPaypalSdk');
    paypalScript.setAttribute('data-namespace', 'paypalSdkPsCheckoutEC');
    paypalScript.setAttribute('data-enable-3ds', '');
    paypalScript.setAttribute('async', '');
    document.head.appendChild(paypalScript);
  }

  loadPaypalScript();
</script>
