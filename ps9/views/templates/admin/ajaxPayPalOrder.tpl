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
<div data-test="checkout-payment-block">
  {if !$orderPayPal}
    <div class="checkout-modal-container">
      <div class="checkout-modal">
        {if $psPayPalOrder->getStatus() === 'CANCELED'}
          <div role="alert" aria-live="polite" aria-atomic="true" class="alert alert-info">
            <p>{l s='Transaction details are not available' d='Modules.Checkout.Pscheckout'} {l s='This PayPal Order has been canceled.' d='Modules.Checkout.Pscheckout'}</p>
          </div>
        {elseif $psPayPalOrder->getStatus() === 'REVERSED'}
          <div role="alert" aria-live="polite" aria-atomic="true" class="alert alert-info">
            <p>{l s='Transaction details are not available' d='Modules.Checkout.Pscheckout'} {l s='This PayPal Order has been reversed.' d='Modules.Checkout.Pscheckout'}</p>
          </div>
        {else}
          <div role="alert" aria-live="polite" aria-atomic="true" class="alert alert-warning">
            <p>{l s='Transaction details are not available' d='Modules.Checkout.Pscheckout'}</p>
          </div>
          <br/>
          <p>{l s='The PayPal account that was used to create this order is no longer linked to the PrestaShop Checkout module.' d='Modules.Checkout.Pscheckout'}</p>
          <p>{l s='In order to see this information, please reconnect the correct PayPal account.' d='Modules.Checkout.Pscheckout'}</p>
          <br/>
          <button class="checkout-modal-button">
            <a class="btn" href="{$moduleUrl}">{l s='Go to PrestaShop Checkout' d='Modules.Checkout.Pscheckout'}</a>
          </button>
        {/if}
      </div>
    </div>
  {/if}

  {if $orderPayPal}
    <div class="panel-wrapper">
      <div class="panel">
        <h3 class="panel__title">{l s='PayPal Order' d='Modules.Checkout.Pscheckout'}</h3>
        <dl class="panel__infos">
          <dt data-grid-area="reference">{l s='Reference' d='Modules.Checkout.Pscheckout'}</dt>
          <dd data-test="reference-value">{$orderPayPal.id|escape:'html':'UTF-8'}</dd>
          <dt data-grid-area="status">{l s='Status' d='Modules.Checkout.Pscheckout'}</dt>
          <dd data-test="status-value">
            <span class="badge rounded badge-{$orderPayPal.status.class|escape:'html':'UTF-8'}" data-value="{$orderPayPal.status.value|escape:'html':'UTF-8'}">
              {$orderPayPal.status.translated|escape:'html':'UTF-8'}
            </span>
          </dd>
          <dt data-grid-area="total">{l s='Total' d='Modules.Checkout.Pscheckout'}</dt>
          <dd data-test="total-value">{$orderPayPal.total}</dd>
          <dt data-grid-area="balance">
            {l s='Balance' d='Modules.Checkout.Pscheckout'}
            <i class="balance-info-icon" title="{l s='Total amount you will receive on your bank account: the order amount, minus transaction fees, minus potential refunds' d='Modules.Checkout.Pscheckout'}"></i>
          </dt>
          <dd data-test="balance-value">{$orderPayPal.balance}</dd>
          <dt data-grid-area="environment">
            {l s='Environment' d='Modules.Checkout.Pscheckout'}
            <i class="environment-info-icon" title="{l s='The environment in which the transaction was made: Test or Production' d='Modules.Checkout.Pscheckout'}"></i>
          </dt>
          <dd data-grid-area="environment-value">
            <span data-test="payment-env-value" class="badge rounded badge-paypal-environment-{if $isProductionEnv}live{else}sandbox{/if}" data-value="{$psPayPalOrder->getEnvironment()|escape:'html':'UTF-8'}">
              {if $isProductionEnv}
                {l s='Production' d='Modules.Checkout.Pscheckout'}
              {else}
                {l s='Test' d='Modules.Checkout.Pscheckout'}
              {/if}
            </span>
          </dd>
          <dt data-grid-area="payment">{l s='Payment mode' d='Modules.Checkout.Pscheckout'}</dt>
          <dd data-test="payment-mode-value">{$orderPayPal.paymentSourceName|escape:'html':'UTF-8'} <img src="{$orderPayPal.paymentSourceLogo}" alt="{$orderPayPal.paymentSourceName|escape:'html':'UTF-8'}" title="{$orderPayPal.paymentSourceName|escape:'html':'UTF-8'}" height="20"></dd>
          {if $psPayPalOrder->getFundingSource() === 'card'}
            <dt data-grid-area="card-sca">{l s='3D Secure' d='Modules.Checkout.Pscheckout'}</dt>
            <dd data-grid-area="card-sca-value">
              {if $orderPayPal.is3DSNotRequired}
                <span class="badge rounded badge-warning">
                  {l s='Not required' d='Modules.Checkout.Pscheckout'}
                </span>
              {elseif $orderPayPal.is3DSecureAvailable && $orderPayPal.isLiabilityShifted}
                <span class="badge rounded badge-success">
                  {l s='Success' d='Modules.Checkout.Pscheckout'}
                </span>
              {elseif $orderPayPal.is3DSecureAvailable && !$orderPayPal.isLiabilityShifted}
                <span class="badge rounded badge-danger">
                  {l s='Failed' d='Modules.Checkout.Pscheckout'}
                </span>
              {else}
                <span class="badge rounded badge-warning">
                  {l s='Card does not support 3D Secure' d='Modules.Checkout.Pscheckout'}
                </span>
              {/if}
            </dd>
            <dt data-grid-area="card-liability">{l s='Liability shift' d='Modules.Checkout.Pscheckout'}</dt>
            <dd data-grid-area="card-liability-value">
              {if $orderPayPal.isLiabilityShifted}
                <span class="badge rounded badge-success">
                  {l s='Bank' d='Modules.Checkout.Pscheckout'}
                </span>
              {else}
                <span class="badge rounded badge-warning">
                  {l s='Merchant' d='Modules.Checkout.Pscheckout'}
                </span>
              {/if}
            </dd>
          {/if}
        </dl>
        {if $psPayPalOrder->getFundingSource() === 'card' && !$orderPayPal.isLiabilityShifted}
          <div class="liability-explanation">
            {if $orderPayPal.is3DSNotRequired}
              {l s='Your 3D Secure settings for this transaction were set to "Strong Customer Authentication (SCA) when required", but the current transaction does not require it as per the regulation.' d='Modules.Checkout.Pscheckout'}
            {/if}
            {l s='The bank issuer declined the liability shift. We advice you not to honor the order immediately, wait a few days in case of chargeback and contact the consumer to ensure authenticity of the transaction. For this type of cases we also recommend to consider Chargeback protection.' d='Modules.Checkout.Pscheckout'}
          </div>
        {/if}
        {if $psPayPalOrder->getFundingSource() === 'card' && $orderPayPal.isLiabilityShifted}
          <div class="liability-explanation">
            {l s='The bank issuer accepted the liability shift. You can safely honor the order.' d='Modules.Checkout.Pscheckout'}
          </div>
        {/if}
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
              <strong class="tab__btn-title">{$orderPayPalTransaction.type.translated|escape:'html':'UTF-8'}</strong>
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
                <h3 class="tabpanel__title">{l s='Transaction details' d='Modules.Checkout.Pscheckout'}</h3>
                <dl class="tabpanel__infos">
                  <dt>{l s='Reference' d='Modules.Checkout.Pscheckout'}</dt>
                  <dd>{$orderPayPalTransaction.id}</dd>
                  <dt>{l s='Status' d='Modules.Checkout.Pscheckout'}</dt>
                  <dd>
                    <span class="badge rounded badge-{$orderPayPalTransaction.status.class|escape:'html':'UTF-8'}">
                      {$orderPayPalTransaction.status.translated|escape:'html':'UTF-8'}
                    </span>
                  </dd>
                  <dt>{l s='Amount (Tax incl.)' d='Modules.Checkout.Pscheckout'}</dt>
                  <dd>{$orderPayPalTransaction.amount} {$orderPayPalTransaction.currency}</dd>
                  {if !empty($orderPayPalTransaction.seller_protection)}
                    <dt>
                      {l s='Seller protection' d='Modules.Checkout.Pscheckout'}
                      <i class="seller-protection-info-icon" title="{$orderPayPalTransaction.seller_protection.help|escape:'html':'UTF-8'}"></i>
                    </dt>
                    <dd>
                    <span class="badge rounded badge-{$orderPayPalTransaction.seller_protection.class|escape:'html':'UTF-8'}">
                      {$orderPayPalTransaction.seller_protection.translated|escape:'html':'UTF-8'}
                    </span>
                    </dd>
                  {/if}
                </dl>
              </div>
              {if $orderPayPalTransaction.gross_amount || $orderPayPalTransaction.paypal_fee || $orderPayPalTransaction.net_amount}
                <div>
                  <h3 class="tabpanel__title">{l s='Transaction amounts' d='Modules.Checkout.Pscheckout'}</h3>
                  <dl class="tabpanel__infos">
                      {if $orderPayPalTransaction.gross_amount}
                    <dt>{l s='Gross amount' d='Modules.Checkout.Pscheckout'}</dt>
                    <dd>{$orderPayPalTransaction.gross_amount} {$orderPayPalTransaction.currency}</dd>
                      {/if}
                      {if $orderPayPalTransaction.paypal_fee}
                    <dt>{l s='Fees (Tax Incl.)' d='Modules.Checkout.Pscheckout'}</dt>
                    <dd>- {$orderPayPalTransaction.paypal_fee} {$orderPayPalTransaction.currency}</dd>
                      {/if}
                      {if $orderPayPalTransaction.net_amount}
                    <dt>{l s='Net amount' d='Modules.Checkout.Pscheckout'}</dt>
                    <dd>{$orderPayPalTransaction.net_amount} {$orderPayPalTransaction.currency}</dd>
                      {/if}
                  </dl>
                </div>
              {/if}
              <a href="https://www.paypal.com/activity/payment/{$orderPayPalTransaction.id|escape:'html':'UTF-8'}" target="_blank" class="tabpanel__cta">
                {l s='See on PayPal' d='Modules.Checkout.Pscheckout'}
              </a>
              {if $orderPayPalTransaction.isRefundable}
                <div class="panel__cta">
                  {l s='Any change on the order?' d='Modules.Checkout.Pscheckout'}
                  <a class="refund" data-transaction-id="{$orderPayPalTransaction.id|escape:'html':'UTF-8'}">
                    {l s='Refund' d='Modules.Checkout.Pscheckout'}
                  </a>
                </div>
              {/if}
            </div>

            {if $orderPayPalTransaction.isRefundable}
              <div id="ps-checkout-refund-{$orderPayPalTransaction.id|escape:'html':'UTF-8'}" class="modal fade ps-checkout-refund" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <form action="{$orderPayPalBaseUrl|escape:'html':'UTF-8'}" method="POST" class="form-horizontal ps-checkout-refund-form">
                      <div class="modal-header">
                        <h5 class="modal-title">
                          <img src="{$moduleLogoUri}" width="20" height="20" alt="logo"> {l s='Refund transaction totally or partially' d='Modules.Checkout.Pscheckout'}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Cancel' d='Modules.Checkout.Pscheckout'}">
                          <span aria-hidden="true">×</span>
                        </button>
                      </div>
                      <div class="modal-body mb-2">
                        <div class="modal-notifications"></div>
                        <div class="modal-content-container">
                          <div class="form-group mb-0">
                            <div class="row">
                              <div class="col-md-12">
                                <p class="mb-2">
                                  <b>{l s='Order details' d='Modules.Checkout.Pscheckout'}</b>
                                </p>
                              </div>
                            </div>
                            <div class="order-totals">
                              <div class='order-totals-column'>
                                <p>{l s='Gross amount' d='Modules.Checkout.Pscheckout'}</p>
                                <p>{l s='Fees (Tax Incl.)' d='Modules.Checkout.Pscheckout'}</p>
                                <p>
                                  <b>{l s='Amount (Tax Incl.)' d='Modules.Checkout.Pscheckout'}</b>
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
                            <div class="row separator"></div>
                            <div class="row">
                              <div class="col-md-6">
                                <label class="form-control-label" for="{$orderPayPalRefundAmountIdentifier|escape:'html':'UTF-8'}">
                                  <b>{l s='Net amount to refund' d='Modules.Checkout.Pscheckout'}</b>
                                </label>
                              </div>
                              <div class="col-md-6">
                                <input name="ajax" type="hidden" value="1">
                                <input name="action" type="hidden" value="RefundOrder">
                                <input name="orderPayPalRefundTransaction" type="hidden" value="{$orderPayPalTransaction.id|escape:'html':'UTF-8'}">
                                <input name="orderPayPalRefundOrder" type="hidden" value="{$orderPayPal.id|escape:'html':'UTF-8'}">
                                <input name="orderPayPalRefundCurrency" type="hidden" value="{$orderPayPalTransaction.currency|escape:'html':'UTF-8'}">
                                <div class="input-group-append">
                                  <div class="input-group-text">{$orderPayPalTransaction.currency|escape:'html':'UTF-8'}</div>
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
                                  {l s='Maximum [AMOUNT_MAX] [CURRENCY] (tax included)' sprintf=['[AMOUNT_MAX]' => $orderPayPalTransaction.maxAmountRefundable|escape:'html':'UTF-8'|string_format:"%.2f", '[CURRENCY]' => $orderPayPalTransaction.currency|escape:'html':'UTF-8'] d='Modules.Checkout.Pscheckout'}
                                  <a href="#">
                                    {l s='Learn more' d='Modules.Checkout.Pscheckout'}
                                  </a>
                                </p>
                              </div>
                            </div>
                          </div>
                          <p class="text-muted">
                            {l s='Your transaction refund request will be sent to PayPal. After that, you’ll need to manually process the refund action in the PrestaShop order: choose the type of refund (standard or partial) in order to generate credit slip.' d='Modules.Checkout.Pscheckout'}
                          </p>
                        </div>
                        <div class="modal-loader text-center">
                          <button class="btn-primary-reverse onclick unbind spinner"></button>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-dismiss="modal">
                          {l s='Cancel' d='Modules.Checkout.Pscheckout'}
                        </button>
                        <button type="button" class="btn btn-primary refund-submit" disabled>
                          {l s='Refund' d='Modules.Checkout.Pscheckout'} <span class="refund-value" data-transaction-currency="{$orderPayPalTransaction.currency|escape:'html':'UTF-8'}"></span>
                        </button>
                        <button type="submit" class="btn btn-primary refund-confirm" hidden="hidden">
                          {l s='Confirm refund' d='Modules.Checkout.Pscheckout'}
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
  {/if}
</div>
