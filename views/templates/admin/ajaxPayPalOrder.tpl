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

<div class="info-block">
  <div class="row mt-3">
    <div class="col-md-6">
      <p class="mb-1">
        <strong>
          {l s='PayPal Order Id' mod='ps_checkout'}
        </strong>
      </p>
      <p>
        {$orderPayPal.id|escape:'html':'UTF-8'}
      </p>
    </div>
    <div class="col-md-6">
      <p class="mb-1">
        <strong>{l s='PayPal Order Status' mod='ps_checkout'}</strong>
      </p>
      <p>
        <span class="badge rounded badge-{$orderPayPal.status.class|escape:'html':'UTF-8'}" data-value="{$orderPayPal.status.value|escape:'html':'UTF-8'}">
          {$orderPayPal.status.translated|escape:'html':'UTF-8'}
        </span>
      </p>
    </div>
  </div>
</div>
{if !empty($orderPayPal.transactions)}
  <table class="table">
    <thead>
    <tr>
      <th>{l s='Date' mod='ps_checkout'}</th>
      <th>{l s='Type' mod='ps_checkout'}</th>
      <th>{l s='Transaction ID' mod='ps_checkout'}</th>
      <th>{l s='Status' mod='ps_checkout'}</th>
      <th>{l s='Amount (Tax included)' mod='ps_checkout'}</th>
      <th></th>
    </tr>
    </thead>
    <tbody>
    {foreach $orderPayPal.transactions as $orderPayPalTransaction}
      <tr>
        <td>
          {dateFormat date=$orderPayPalTransaction.date full=true}
        </td>
        <td>
        <span class="badge rounded badge-{$orderPayPalTransaction.type.class|escape:'html':'UTF-8'}" data-value="{$orderPayPalTransaction.type.value|escape:'html':'UTF-8'}">
        {$orderPayPalTransaction.type.translated|escape:'html':'UTF-8'}
      </span>
        </td>
        <td>
          {$orderPayPalTransaction.id|escape:'html':'UTF-8'}
        </td>
        <td>
        <span class="badge rounded badge-{$orderPayPalTransaction.status.class|escape:'html':'UTF-8'}" data-value="{$orderPayPalTransaction.status.value|escape:'html':'UTF-8'}">
        {$orderPayPalTransaction.status.translated|escape:'html':'UTF-8'}
      </span>
        </td>
        <td>
          {$orderPayPalTransaction.amount|escape:'html':'UTF-8'} {$orderPayPalTransaction.currency|escape:'html':'UTF-8'}
        </td>
        <td class="text-right">
          {if $orderPayPalTransaction.isRefundable}
            <button type="button" class="btn btn-primary btn-sm refund" data-transaction-id="{$orderPayPalTransaction.id|escape:'html':'UTF-8'}">
              {l s='Refund' mod='ps_checkout'}
            </button>
          {/if}
          <a class="btn btn-sm btn-outline-secondary" target="_blank" href="https://www.paypal.com/activity/payment/{$orderPayPalTransaction.id|escape:'html':'UTF-8'}">
            {l s='Details' mod='ps_checkout'}
          </a>
        </td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  {foreach $orderPayPal.transactions as $orderPayPalTransaction}
    {if $orderPayPalTransaction.isRefundable}
      {assign var="maxAmountRefundable" value=$orderPayPalTransaction.maxAmountRefundable|string_format:"%.2f"}
      {assign var="orderPayPalRefundAmountIdentifier" value='orderPayPalRefundAmount'|cat:$orderPayPalTransaction.id}
      <div id="ps-checkout-refund-{$orderPayPalTransaction.id|escape:'html':'UTF-8'}" class="modal fade ps-checkout-refund" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <form action="{$orderPayPalBaseUrl|escape:'html':'UTF-8'}" method="POST" class="form-horizontal ps-checkout-refund-form">
              <div class="modal-header">
                <h5 class="modal-title">
                  {$moduleName|escape:'html':'UTF-8'} - {l s='Refund' mod='ps_checkout'}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Cancel' mod='ps_checkout'}">
                  <span aria-hidden="true">×</span>
                </button>
              </div>
              <div class="modal-body mb-2">
                <div class="modal-notifications">
                </div>
                <div class="modal-content-container">
                  <input name="ajax" type="hidden" value="1">
                  <input name="action" type="hidden" value="RefundOrder">
                  <input name="orderPayPalRefundTransaction" type="hidden" value="{$orderPayPalTransaction.id|escape:'html':'UTF-8'}">
                  <input name="orderPayPalRefundOrder" type="hidden" value="{$orderPayPal.id|escape:'html':'UTF-8'}">
                  <input name="orderPayPalRefundCurrency" type="hidden" value="{$orderPayPalTransaction.currency|escape:'html':'UTF-8'}">
                  <p class="text-muted">
                    {l s='Your transaction refund request will be sent to PayPal. After that, you’ll need to manually process the refund action in the PrestaShop order: choose the type of refund (standard or partial) in order to generate credit slip.' mod='ps_checkout'}
                  </p>
                  <div class="form-group mb-0">
                    <label class="form-control-label" for="{$orderPayPalRefundAmountIdentifier|escape:'html':'UTF-8'}">
                      {l s='Choose amount to refund (tax included)' mod='ps_checkout'}
                    </label>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="input-group-append">
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
                          <div class="input-group-text">{$orderPayPalTransaction.currency|escape:'html':'UTF-8'}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <p class="text-muted">
                    {l s='Maximum [AMOUNT_MAX] [CURRENCY] (tax included)' sprintf=['[AMOUNT_MAX]' => $orderPayPalTransaction.maxAmountRefundable|escape:'html':'UTF-8'|string_format:"%.2f", '[CURRENCY]' => $orderPayPalTransaction.currency|escape:'html':'UTF-8'] mod='ps_checkout'}
                  </p>
                </div>
                <div class="modal-loader text-center">
                  <button class="btn-primary-reverse onclick unbind spinner"></button>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                  {l s='Cancel' mod='ps_checkout'}
                </button>
                <button type="submit" class="btn btn-primary">
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
  #ps_checkout .badge.badge-payment {
    background-color: #00B887;
    color: #fff;
  }

  #ps_checkout .badge.badge-refund {
    background-color: #34219E;
    color: #fff;
  }
</style>
