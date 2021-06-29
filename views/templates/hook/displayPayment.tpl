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

{if !$isExpressCheckout}
  <div id="ps_checkout-loader" class="express-checkout-block mb-2">
    <div class="express-checkout-block-wrapper">
      <p class="express-checkout-spinner-text">
        {$loaderTranslatedText|escape:'htmlall':'UTF-8'}
      </p>
      <div class="express-checkout-spinner">
        <img src="{$spinnerPath|escape:'htmlall':'UTF-8'}" alt="{$loaderTranslatedText|escape:'htmlall':'UTF-8'}">
      </div>
    </div>
  </div>
{/if}

<section id="ps_checkout-displayPayment">
  {if !$is17 && $isExpressCheckout}
  <div class="express-checkout-block">
    <img src="{$paypalLogoPath|escape:'htmlall':'UTF-8'}" class="express-checkout-img" alt="PayPal">
    <p class="express-checkout-label">
      {$translatedText|escape:'htmlall':'UTF-8'}
    </p>
    <div id="button-paypal" class="ps_checkout-express-checkout-button">
      <button id="ps_checkout-express-checkout-submit-button" class="button btn btn-default button-medium" type="button" disabled>
        <span>{l s='I confirm my order' mod='ps_checkout'}<i class="icon-chevron-right right"></i></span>
      </button>
    </div>
  </div>
  {/if}
  <div class="payment-options">
    {foreach from=$paymentOptions item="paymentOptionName" key="fundingSource"}
      <div id="payment-option-{$fundingSource}-container" class="payment-option ps_checkout-payment-option row" style="display: none;">
        <div class="col-xs-12">
          <div id="payment-option-{$fundingSource}" class="payment_module closed" data-module-name="ps_checkout-{$fundingSource}">
            <a class="ps_checkout-{$fundingSource}" href="#">
              {$paymentOptionName}
            </a>
          </div>
          <div class="payment_module closed">
            <a>
              {if $fundingSource == 'card' && $isHostedFieldsAvailable}
                <form id="ps_checkout-hosted-fields-form" class="form-horizontal">
                  <div class="form-group">
                    <label class="form-control-label" for="ps_checkout-hosted-fields-card-number">{l s='Card number' mod='ps_checkout'}</label>
                    <div id="ps_checkout-hosted-fields-card-number" class="form-control">
                      <div id="card-image">
                        <img class="defautl-credit-card" src="{$modulePath}views/img/credit_card.png" alt="">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-xs-6">
                      <label class="form-control-label" for="ps_checkout-hosted-fields-card-expiration-date">{l s='Expiry date' mod='ps_checkout'}</label>
                      <div id="ps_checkout-hosted-fields-card-expiration-date" class="form-control"></div>
                    </div>
                    <div class="form-group col-xs-6">
                      <label class="form-control-label" for="ps_checkout-hosted-fields-card-cvv">{l s='CVC' mod='ps_checkout'}</label>
                      <div class="ps_checkout-info-wrapper">
                        <div class="ps_checkout-info-button" onmouseenter="cvvEnter()" onmouseleave="cvvLeave()">i
                          <div class="popup-content" id="cvv-popup">
                            <img src="{$modulePath}views/img/cvv.svg" alt="">
                            {l s='The security code is a' mod='ps_checkout'} <b>{l s='3-digits' mod='ps_checkout'}</b> {l s='code on the back of your credit card. In some cases, it can be 4-digits or on the front of your card.' mod='ps_checkout'}
                          </div>
                        </div>
                      </div>
                      <div id="ps_checkout-hosted-fields-card-cvv" class="form-control"></div>
                    </div>
                  </div>

                  <div id="payment-confirmation" class="submit hidden">
                    <button id="hosted-fields-validation" class="button btn btn-default button-medium" type="submit">
                      <span>{l s='I confirm my order' mod='ps_checkout'}<i class="icon-chevron-right right"></i></span>
                    </button>
                  </div>
                </form>
              {/if}
              <div class="js-payment-ps_checkout-card">
                <div class="ps_checkout-button" data-funding-source="{$fundingSource}"></div>
              </div>
            </a>
          </div>
        </div>
      </div>
    {/foreach}
  </div>
</section>
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
