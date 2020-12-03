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

<section id="ps_checkout-displayPayment">
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
