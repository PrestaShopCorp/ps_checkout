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
      <div class="payment-option row" style="display: none;">
        <div class="col-xs-12">
          <p class="payment_module closed">
            <a class="pscheckout-{$fundingSource}" href="#">
              <div id="{$fundingSource}-mark" style="display: inline-block;">
                {if $fundingSource == 'card'}
                  <img class="ps-checkout-funding-img" alt="{$fundingSource}" src="{$modulePath}views/img/payment-cards.png">
                {/if}
              </div>
              {$paymentOptionName}
            </a>
          </p>
          <p class="payment_module closed">
            <a>
              <div id="button-{$fundingSource}" class="checkout-smartbutton">
                {if $fundingSource == 'card' && $isHostedFieldsAvailable}
                  <form id="ps_checkout-hosted-fields-form" class="form-horizontal" style="display:none;">
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
              </div>
            </a>
          </p>
        </div>
      </div>
    {/foreach}
  </div>
</section>
