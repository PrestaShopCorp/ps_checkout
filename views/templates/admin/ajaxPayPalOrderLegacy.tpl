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

<div class="well row" xmlns="http://www.w3.org/1999/html">
  <div class="col-xs-6">
    <dl class="list-detail">
      <dt>
        {l s='PayPal Order Id:' mod='ps_checkout'}
      </dt>
      <dd>
        {$orderPayPal.id|escape:'html':'UTF-8'}
      </dd>
    </dl>
  </div>
  <div class="col-xs-6">
    <dl class="list-detail">
      <dt>
        {l s='PayPal Order Status:' mod='ps_checkout'}
      </dt>
      <dd>
        <span class="span label label-{$orderPayPal.status.class|escape:'html':'UTF-8'}" data-value="{$orderPayPal.status.value|escape:'html':'UTF-8'}">
          {$orderPayPal.status.translated|escape:'html':'UTF-8'}
        </span>
      </dd>
    </dl>
  </div>
</div>
{if !empty($orderPayPal.transactions)}
  <div class="table-responsive">
    <table class="table">
      <thead>
      <tr>
        <th><span class="title_box">{l s='Date' mod='ps_checkout'}</span></th>
        <th><span class="title_box">{l s='Type' mod='ps_checkout'}</span></th>
        <th><span class="title_box">{l s='Transaction ID' mod='ps_checkout'}</span></th>
        <th><span class="title_box">{l s='Status' mod='ps_checkout'}</span></th>
        <th><span class="title_box">{l s='Amount (Tax included)' mod='ps_checkout'}</span></th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      {foreach $orderPayPal.transactions as $orderPayPalTransaction}
        <tr>
          <td>{dateFormat date=$orderPayPalTransaction.date full=true}</td>
          <td>
            <span class="span label label-{$orderPayPalTransaction.type.class|escape:'html':'UTF-8'}" data-value="{$orderPayPalTransaction.type.value|escape:'html':'UTF-8'}">
              {$orderPayPalTransaction.type.translated|escape:'html':'UTF-8'}
            </span>
          </td>
          <td>{$orderPayPalTransaction.id|escape:'html':'UTF-8'}</td>
          <td>
            <span class="span label label-{$orderPayPalTransaction.status.class|escape:'html':'UTF-8'}" data-value="{$orderPayPalTransaction.status.value|escape:'html':'UTF-8'}">
              {$orderPayPalTransaction.status.translated|escape:'html':'UTF-8'}
            </span>
          </td>
          <td>{$orderPayPalTransaction.amount|escape:'html':'UTF-8'} {$orderPayPalTransaction.currency|escape:'html':'UTF-8'}</td>
          <td class="actions">
            {if $orderPayPalTransaction.isRefundable}
              <button type="button" class="btn btn-primary refund" data-transaction-id="{$orderPayPalTransaction.id|escape:'html':'UTF-8'}">
                <i class="icon-exchange"></i>
                {l s='Refund' mod='ps_checkout'}
              </button>
            {/if}
            <a class="btn btn-default" target="_blank" href="https://www.paypal.com/activity/payment/{$orderPayPalTransaction.id|escape:'html':'UTF-8'}">
              <i class="icon-search"></i>
              {l s='Details' mod='ps_checkout'}
            </a>
          </td>
        </tr>
      {/foreach}
      </tbody>
    </table>
  </div>
  {foreach $orderPayPal.transactions as $orderPayPalTransaction}
    {if $orderPayPalTransaction.isRefundable}
      {assign var="maxAmountRefundable" value=$orderPayPalTransaction.maxAmountRefundable|string_format:"%.2f"}
      {assign var="orderPayPalRefundAmountIdentifier" value='orderPayPalRefundAmount'|cat:$orderPayPalTransaction.id}
      <div id="ps-checkout-refund-{$orderPayPalTransaction.id|escape:'html':'UTF-8'}" class="modal ps-checkout-refund fade in" aria-hidden="false">
        <div class="modal-dialog">
          <div class="modal-content">
            <form action="{$orderPayPalBaseUrl|escape:'html':'UTF-8'}" method="POST" class="form-horizontal ps-checkout-refund-form">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 class="modal-title">
                  {$moduleName|escape:'html':'UTF-8'} - {l s='Refund' mod='ps_checkout'}
                </h3>
              </div>
              <div class="modal-body">
                <div class="modal-notifications">
                </div>
                <div class="modal-content-container">
                  <input name="ajax" type="hidden" value="1">
                  <input name="action" type="hidden" value="RefundOrder">
                  <input name="orderPayPalRefundTransaction" type="hidden" value="{$orderPayPalTransaction.id|escape:'html':'UTF-8'}">
                  <input name="orderPayPalRefundOrder" type="hidden" value="{$orderPayPal.id|escape:'html':'UTF-8'}">
                  <input name="orderPayPalRefundCurrency" type="hidden" value="{$orderPayPalTransaction.currency|escape:'html':'UTF-8'}">
                  <p class="help-block">
                    {l s='Your transaction refund request will be sent to PayPal. After that, you’ll need to manually process the refund action in the PrestaShop order: choose the type of refund (standard or partial) in order to generate credit slip.' mod='ps_checkout'}
                  </p>
                  <div class="form-group">
                    <label class="control-label" for="{$orderPayPalRefundAmountIdentifier|escape:'html':'UTF-8'}">
                      {l s='Choose amount to refund (tax included)' mod='ps_checkout'}
                    </label>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="input-group">
                          <input
                                  class="form-control text-right"
                                  name="orderPayPalRefundAmount"
                                  id="{$orderPayPalRefundAmountIdentifier|escape:'html':'UTF-8'}"
                                  type="number"
                                  step=".01"
                                  min="0.01"
                                  max="{$maxAmountRefundable|escape:'html':'UTF-8'}"
                                  value="{$maxAmountRefundable|escape:'html':'UTF-8'}"
                          >
                          <div class="input-group-addon">{$orderPayPalTransaction.currency|escape:'html':'UTF-8'}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <p class="help-block">
                    {* Function l of smarty not support sprintf in PrestaShop 1.6 *}
                    {capture assign="refundHelpText"}
                      {l s='Maximum [AMOUNT_MAX] [CURRENCY] (tax included)' mod='ps_checkout'}
                    {/capture}
                    {$refundHelpText|replace:'[AMOUNT_MAX]':$maxAmountRefundable|replace:'[CURRENCY]':$orderPayPalTransaction.currency}
                  </p>
                </div>
                <div class="modal-loader">
                  <i class="process-icon-loading"></i>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">
                  {l s='Cancel' mod='ps_checkout'}
                </button>
                <button type="submit" class="btn btn-primary btn-lg">
                  {l s='Refund' mod='ps_checkout'}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    {/if}
  {/foreach}
{/if}

<style>
  #ps_checkout dl.list-detail {
    margin-bottom: 0;
  }

  #ps_checkout dl.list-detail dt {
    margin-bottom: .3125rem;
  }

  #ps_checkout .label.label-payment {
    background-color: #00B887;
  }

  #ps_checkout .label.label-refund {
    background-color: #34219E;
  }
</style>
