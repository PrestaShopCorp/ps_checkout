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

<div id="ps_checkout" class="panel">
  <div class="panel-heading">
    <img src="{$moduleLogoUri|escape:'html':'UTF-8'}" alt="{$moduleName|escape:'html':'UTF-8'}" width="15" height="15">
    {$moduleName|escape:'html':'UTF-8'}
  </div>
  {if $orderPayPalRefundSuccess}
    <div class="alert alert-success">{$orderPayPalRefundSuccess|escape:'html':'UTF-8'}</div>
  {/if}
  {if $orderPayPalRefundErrors}
    <div class="alert alert-danger">
      <ul>
        {foreach $orderPayPalRefundErrors as $orderPayPalRefundError}
          <li>{$orderPayPalRefundError|escape:'html':'UTF-8'}</li>
        {/foreach}
      </ul>
      {$orderPayPalRefundSuccess|escape:'html':'UTF-8'}
    </div>
  {/if}
  <div class="row">
    <div class="col-xs-6">
      <strong>{l s='PayPal Order Id' mod='ps_checkout'}</strong> {$orderPayPalId|escape:'html':'UTF-8'}
    </div>
    <div class="col-xs-6">
      <strong>{l s='PayPal Order Status' mod='ps_checkout'}</strong> {$orderPayPalStatus|escape:'html':'UTF-8'}
    </div>
  </div>
  {if !empty($orderPayPalTransactions)}
    <hr>
    <div class="table-responsive">
      <table class="table">
        <thead>
        <tr>
          <th><span class="title_box">{l s='Type' mod='ps_checkout'}</span></th>
          <th><span class="title_box">{l s='Transaction ID' mod='ps_checkout'}</span></th>
          <th><span class="title_box">{l s='Date' mod='ps_checkout'}</span></th>
          <th><span class="title_box">{l s='Status' mod='ps_checkout'}</span></th>
          <th><span class="title_box">{l s='Amount' mod='ps_checkout'}</span></th>
          <th></th>
        </tr>
        </thead>
        <tbody>
        {foreach $orderPayPalTransactions as $orderPayPalTransaction}
          <tr>
            <td>{$orderPayPalTransaction.type|escape:'html':'UTF-8'}</td>
            <td>{$orderPayPalTransaction.id|escape:'html':'UTF-8'}</td>
            <td>{dateFormat date=$orderPayPalTransaction.date full=true}</td>
            <td>{$orderPayPalTransaction.status|escape:'html':'UTF-8'}</td>
            <td>{$orderPayPalTransaction.amount|escape:'html':'UTF-8'} {$orderPayPalTransaction.currency|escape:'html':'UTF-8'}</td>
            <td class="actions">
              <a class="btn btn-default" target="_blank" href="https://www.paypal.com/activity/payment/{$orderPayPalTransaction.id|escape:'html':'UTF-8'}">
                <i class="icon-search"></i>
                {l s='Details' mod='ps_checkout'}
              </a>
              {if $orderPayPalTransaction.isRefundable}
                <hr>
              <form class="form-horizontal form-inline orderPayPalRefundForm" method="post" action="{$orderPayPalRefundUrl|escape:'html':'UTF-8'}">
                <input type="hidden" name="orderPayPalRefundTransaction" value="{$orderPayPalTransaction.id|escape:'html':'UTF-8'}">
                <input type="hidden" name="orderPayPalRefundCurrency" value="{$orderPayPalTransaction.currency|escape:'html':'UTF-8'}">
                <div class="input-group">
                  <input name="orderPayPalRefundAmount" type="number" step=".01" min="0" max="{$orderPayPalTransaction.maxAmountRefundable|escape:'html':'UTF-8'|string_format:"%.2f"}" value="{$orderPayPalTransaction.maxAmountRefundable|escape:'html':'UTF-8'|string_format:"%.2f"}" class="form-control">
                  <div id="orderPayPalRefundCurrencyContainer" class="input-group-addon">{$orderPayPalTransaction.currency|escape:'html':'UTF-8'}</div>
                </div>
                <button type="submit" name="orderPayPalRefundSubmit" class="btn btn-default">
                  <i class="icon-exchange"></i>
                  {l s='Refund' mod='ps_checkout'}
                </button>
              </form>
              {/if}
            </td>
          </tr>
        {/foreach}
        </tbody>
      </table>
    </div>
  {/if}
</div>

<script>
  $(document).ready(function() {
    $(document).on('submit', '.orderPayPalRefundForm', function () {
      let isApproved = confirm("{l s='Refund request will be sent to PayPal, you have to change PrestaShop order status yourself.' mod='ps_checkout'}");

      if (isApproved) {
        $('button[name="orderPayPalRefundSubmit"]').html('<i class="process-icon-loading"></i>').prop('disabled', true);
      }

      return isApproved;
    });
  });
</script>
