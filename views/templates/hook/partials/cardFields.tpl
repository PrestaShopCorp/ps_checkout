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

{**
 * WARNING
 *
 * This file allow only html
 *
 * It will be parsed by PrestaShop Core with PrestaShop\PrestaShop\Core\Payment\PaymentOptionFormDecorator
 *
 * Script tags will be removed and some HTML5 element can cause an Exception due to DOMDocument class
 *}
<div>

<form id="ps_checkout-card-fields-form" class="form-horizontal loading">
  <div id="ps_checkout-card-fields-form-loader">
    <img src="{$modulePath}views/img/tail-spin.svg" alt="">
  </div>
  <div>
    <label class="form-control-label" for="ps_checkout-card-fields-name">{l s='Cardholder Name (optional)' mod='ps_checkout'}</label>
    <div id="ps_checkout-card-fields-name"></div>
    <div id="ps_checkout-card-fields-name-error" class="alert alert-danger hidden">{l s='Card holder name is invalid' mod='ps_checkout'}</div>
  </div>
  <div>
    <label class="form-control-label" for="ps_checkout-card-fields-number">{l s='Card number' mod='ps_checkout'}</label>
    <div id="ps_checkout-card-fields-number" ></div>
    <div id="ps_checkout-card-fields-number-error" class="alert alert-danger hidden">{l s='Card number is invalid' mod='ps_checkout'}</div>
    <div id="ps_checkout-card-fields-vendor-error" class="alert alert-danger hidden">{l s='Card vendor is invalid' mod='ps_checkout'}</div>
  </div>
  <div class="row">
    <div class="col-xs-6 col-6">
      <label class="form-control-label" for="ps_checkout-card-fields-expiry">{l s='Card expiration date' mod='ps_checkout'}</label>
      <div id="ps_checkout-card-fields-expiry" ></div>
      <div id="ps_checkout-card-fields-expiry-error" class="alert alert-danger hidden">{l s='Card expiration date is invalid' mod='ps_checkout'}</div>
    </div>
    <div class="col-xs-6 col-6">
      <div class="ps_checkout-card-fields-cvv-label-wrapper">
        <label class="form-control-label" for="ps_checkout-card-fields-cvv">{l s='CVC' mod='ps_checkout'}</label>
        <div class="ps_checkout-info-wrapper">
          <div class="ps_checkout-info-button" onmouseenter="cvvEnter()" onmouseleave="cvvLeave()">i
            <div class="popup-content" id="cvv-popup">
              <img src="{$modulePath}views/img/cvv.svg" alt="">
              {l s='The security code is a' mod='ps_checkout'} <b>{l s='3-digits' mod='ps_checkout'}</b> {l s='code on the back of your credit card. In some cases, it can be 4-digits or on the front of your card.' mod='ps_checkout'}
            </div>
          </div>
        </div>
      </div>
      <div id="ps_checkout-card-fields-cvv" ></div>
      <div id="ps_checkout-card-fields-cvv-error" class="alert alert-danger hidden">{l s='CVV code is invalid' mod='ps_checkout'}</div>
    </div>
  </div>
  {if $vaultingEnabled}
    {include file='module:ps_checkout/views/templates/hook/partials/vaultPaymentFields.tpl' paymentIdentifier='card'}
  {/if}
  <div id="payments-sdk__contingency-lightbox"></div>
</form>
</div>

<script>
  function cvvEnter() {
    var popup = document.getElementById("cvv-popup");
    popup.classList.add("show");
  }
  function cvvLeave() {
    var popup = document.getElementById("cvv-popup");
    popup.classList.remove("show");
  }
</script>
