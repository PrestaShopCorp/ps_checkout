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
<div class="legacy">
  <div class="panel-wrapper">
    <div>
      <h3 class="panel__title">{l s='PayPal Order' mod='ps_checkout'}</h3>
      <dl class="panel__infos">
        <dt data-grid-area="reference">{l s='Reference' mod='ps_checkout'}</dt>
        <dd>{$orderPayPal.id|escape:'html':'UTF-8'}</dd>
        <dt data-grid-area="status">{l s='Status' mod='ps_checkout'}</dt>
        <dd>
          <span class="badge rounded badge-{$orderPayPal.status.class|escape:'html':'UTF-8'}" data-value="{$orderPayPal.status.value|escape:'html':'UTF-8'}">
            {$orderPayPal.status.translated|escape:'html':'UTF-8'}
          </span>
        </dd>
        <dt data-grid-area="total">{l s='Total' mod='ps_checkout'}</dt>
        <dd>{$orderPayPal.total}</dd>
        <dt data-grid-area="balance">
            {l s='Balance' mod='ps_checkout'}
          <i class="icon-info-sign" title="{l s='Total amount you will receive on your bank account: the order amount, minus transaction fees, minus potential refunds' mod='ps_checkout'}"></i>
        </dt>
        <dd>{$orderPayPal.balance}</dd>
        <dt data-grid-area="payment">{l s='Payment mode' mod='ps_checkout'}</dt>
        <dd>{$orderPaymentDisplayName|escape:'html':'UTF-8'} <img src="{$orderPaymentLogoUri}" alt="{$orderPaymentDisplayName|escape:'html':'UTF-8'}" title="{$orderPaymentDisplayName|escape:'html':'UTF-8'}" height="20"></dd>
      </dl>
    </div>
  </div>
    {if !empty($orderPayPal.transactions)}
      <div class="select-wrapper">
        <select name="select-tab" id="select-transaction" class="select-wrapper__select">
            {foreach $orderPayPal.transactions as $orderPayPalTransaction}
              <option value="{$orderPayPalTransaction.id}-tab">{dateFormat date=$orderPayPalTransaction.date full=true} - {$orderPayPalTransaction.type.translated|escape:'html':'UTF-8'} | {if $orderPayPalTransaction.type.value === 'refund'}-{else}+{/if} {$orderPayPalTransaction.amount|escape:'html':'UTF-8'} {$orderPayPalTransaction.currency|escape:'html':'UTF-8'}</option>
            {/foreach}
        </select>
      </div>

      <div class="tabs">
        <div role="tablist" aria-label="Transactions">
            {assign var="counter" value=1}
            {foreach $orderPayPal.transactions as $orderPayPalTransaction}
              <button
                role="tab"
                aria-selected="{if $counter eq 1}true{else}false{/if}"
                aria-controls="{$orderPayPalTransaction.id}-tab"
                class="tab"
              >
                <strong class="tab__btn-title"> {$orderPayPalTransaction.type.translated|escape:'html':'UTF-8'} </strong>
                <span class="tab__btn-infos">
                <span class="tab__btn-time">{dateFormat date=$orderPayPalTransaction.date full=true}</span>
                <strong class="tab__btn-amount">
                    {if $orderPayPalTransaction.type.value === 'refund'}-{else}+{/if}
                    {$orderPayPalTransaction.amount|escape:'html':'UTF-8'} {$orderPayPalTransaction.currency|escape:'html':'UTF-8'}
                </strong>
              </span>
              </button>
                {assign var="counter" value=$counter+1}
            {/foreach}
        </div>

        <div class="tabpanel-wrapper">
            {assign var="counter" value=1}
            {foreach $orderPayPal.transactions as $orderPayPalTransaction}
                {assign var="maxAmountRefundable" value=$orderPayPalTransaction.maxAmountRefundable|string_format:"%.2f"}
                {assign var="orderPayPalRefundAmountIdentifier" value='orderPayPalRefundAmount'|cat:$orderPayPalTransaction.id}
              <div
                tabindex="0"
                role="tabpanel"
                id="{$orderPayPalTransaction.id}-tab"
                aria-labelledby="first"
                class="tabpanel"
                {if $counter neq 1}hidden="hidden"{/if}
              >
                <div>
                  <div>
                    <h3 class="tabpanel__title">{l s='Transaction details' mod='ps_checkout'}</h3>
                    <dl class="tabpanel__infos">
                      <dt>{l s='Reference' mod='ps_checkout'}</dt>
                      <dd>{$orderPayPalTransaction.id}</dd>
                      <dt>{l s='Status' mod='ps_checkout'}</dt>
                      <dd>
                        <span class="badge rounded badge-{$orderPayPalTransaction.status.class|escape:'html':'UTF-8'}">
                          {$orderPayPalTransaction.status.translated}
                        </span>
                      </dd>
                      <dt>{l s='Amount (Tax incl.)' mod='ps_checkout'}</dt>
                      <dd>{$orderPayPalTransaction.amount} {$orderPayPalTransaction.currency}</dd>
                    </dl>
                  </div>
                  <div>
                    <h3 class="tabpanel__title">{l s='Transaction amounts' mod='ps_checkout'}</h3>
                    <dl class="tabpanel__infos">
                      <dt>{l s='Gross amount' mod='ps_checkout'}</dt>
                      <dd>{$orderPayPalTransaction.gross_amount} {$orderPayPalTransaction.currency}</dd>
                      <dt>{l s='Fees (Tax Incl.)' mod='ps_checkout'}</dt>
                      <dd>- {$orderPayPalTransaction.paypal_fee} {$orderPayPalTransaction.currency}</dd>
                      <dt>{l s='Net amount' mod='ps_checkout'}</dt>
                      <dd>{$orderPayPalTransaction.net_amount} {$orderPayPalTransaction.currency}</dd>
                    </dl>
                  </div>
                  <a href="https://www.paypal.com/activity/payment/{$orderPayPalTransaction.id|escape:'html':'UTF-8'}" target="_blank" class="tabpanel__cta">
                      {l s='See on PayPal' mod='ps_checkout'}
                  </a>
                    {if $orderPayPalTransaction.isRefundable}
                      <a class="btn btn-primary btn-sm refund" data-transaction-id="{$orderPayPalTransaction.id|escape:'html':'UTF-8'}">
                          {l s='Refund' mod='ps_checkout'}
                      </a>
                    {/if}
                </div>
              </div>

                {if $orderPayPalTransaction.isRefundable}
                  <div id="ps-checkout-refund-{$orderPayPalTransaction.id|escape:'html':'UTF-8'}" class="modal fade ps-checkout-refund legacy" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <form action="{$orderPayPalBaseUrl|escape:'html':'UTF-8'}" method="POST" class="form-horizontal ps-checkout-refund-form">
                          <div class="modal-header">
                            <h3 class="modal-title">
                              <img src="{$moduleLogoUri}" width="20" height="20" alt="logo"> {l s='Refund transaction totally or partially' mod='ps_checkout'}
                            </h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Cancel' mod='ps_checkout'}">
                              <span aria-hidden="true">×</span>
                            </button>
                          </div>
                          <div class="modal-body mb-2">
                            <div class="modal-notifications">
                            </div>
                            <div class="modal-content-container">
                              <div class="form-group mb-0">
                                <div class="row">
                                  <div class="col-md-12">
                                    <p class="mb-2">
                                      <b>{l s='Order details' mod='ps_checkout'}</b>
                                    </p>
                                  </div>
                                </div>
                                <div class="order-totals">
                                  <div class='order-totals-column'>
                                    <p>{l s='Gross amount' mod='ps_checkout'}</p>
                                    <p>{l s='Fees (Tax Incl.)' mod='ps_checkout'}</p>
                                    <p>
                                      <b>{l s='Amount (Tax Incl.)' mod='ps_checkout'}</b>
                                    </p>
                                  </div>
                                  <div class='order-totals-column'>
                                    <p>
                                      <b>{$orderPayPal.total}</b>
                                    </p>
                                    <p>
                                      <b>{$orderPayPal.fees}</b>
                                    </p>
                                    <p>
                                      <b>{$orderPayPal.balance}</b>
                                    </p>
                                  </div>
                                </div>
                                <div class="row separator">

                                </div>
                                <div class="row">
                                  <div class="col-md-6">
                                    <label class="form-control-label" for="{$orderPayPalRefundAmountIdentifier|escape:'html':'UTF-8'}">
                                      <b>{l s='Net amount to refund' mod='ps_checkout'}</b>
                                    </label>
                                  </div>
                                  <div class="col-md-6">
                                    <input name="ajax" type="hidden" value="1">
                                    <input name="action" type="hidden" value="RefundOrder">
                                    <input name="orderPayPalRefundTransaction" type="hidden" value="{$orderPayPalTransaction.id|escape:'html':'UTF-8'}">
                                    <input name="orderPayPalRefundOrder" type="hidden" value="{$orderPayPal.id|escape:'html':'UTF-8'}">
                                    <input name="orderPayPalRefundCurrency" type="hidden" value="{$orderPayPalTransaction.currency|escape:'html':'UTF-8'}">
                                    <div class="input-group">
                                      <div class="input-group-text"></div>
                                      <div class="input-group-addon">{$orderPayPalTransaction.currency|escape:'html':'UTF-8'}</div>
                                      <input
                                        class="form-control text-right"
                                        name="orderPayPalRefundAmount"
                                        id="{$orderPayPalRefundAmountIdentifier|escape:'html':'UTF-8'}"
                                        type="number"
                                        step=".01"
                                        min="0.01"
                                        max="{$maxAmountRefundable|escape:'html':'UTF-8'}"
                                      >
                                    </div>
                                    <p class="text-muted">
                                        {* Function l of smarty not support sprintf in PrestaShop 1.6 *}
                                        {capture assign="refundHelpText"}
                                            {l s='Maximum [AMOUNT_MAX] [CURRENCY] (tax included)' mod='ps_checkout'}
                                        {/capture}
                                        {$refundHelpText|replace:'[AMOUNT_MAX]':$maxAmountRefundable|replace:'[CURRENCY]':$orderPayPalTransaction.currency}                                      <a href="#">
                                          {l s='Learn more' mod='ps_checkout'}
                                      </a>
                                    </p>
                                  </div>
                                </div>
                              </div>
                              <p class="text-muted">
                                  {l s='Your transaction refund request will be sent to PayPal. After that, you’ll need to manually process the refund action in the PrestaShop order: choose the type of refund (standard or partial) in order to generate credit slip.' mod='ps_checkout'}
                              </p>
                            </div>
                            <div class="modal-loader text-center">
                              <i class="process-icon-loading"></i>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-outline-primary" data-dismiss="modal">
                                {l s='Cancel' mod='ps_checkout'}
                            </button>
                            <button type="button" class="btn btn-primary refund-submit" disabled>
                                {l s='Refund' mod='ps_checkout'} <span class="refund-value" data-transaction-currency="{$orderPayPalTransaction.currency|escape:'html':'UTF-8'}"></span>
                            </button>
                            <button type="submit" class="btn btn-primary refund-confirm" hidden="hidden">
                                {l s='Confirm refund' mod='ps_checkout'}
                            </button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                {/if}

                {assign var="counter" value=$counter+1}
            {/foreach}
        </div>
      </div>
    {/if}
</div>
