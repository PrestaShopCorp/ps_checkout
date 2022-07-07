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
{if $isShop17}
  <section id="ps_checkout-displayOrderDetail" class="box">
    <h3 class="h1 card-title">
        {l s='Payment gateway information' mod='ps_checkout'}
    </h3>
      {if !$orderIsPaid}
        <div class="alert alert-warning">
            {l s='The order has not yet been paid' mod='ps_checkout'}
        </div>
        <p>{l s='We have not received information about the successful payment of the order.' mod='ps_checkout'}</p>
      {/if}
    <div class="definition-list">
      <dl>
        <dt>{l s='Funding source' mod='ps_checkout'}</dt>
        <dd>{$orderPayPalFundingSource|escape:'html':'UTF-8'}</dd>
          {if $orderPayPalTransactionId}
            <dt>{l s='Transaction identifier' mod='ps_checkout'}</dt>
            <dd>{$orderPayPalTransactionId|escape:'html':'UTF-8'}</dd>
            <dt>{l s='Transaction status' mod='ps_checkout'}</dt>
            <dd>{$orderPayPalTransactionStatus|escape:'html':'UTF-8'}</dd>
          {/if}
      </dl>
    </div>
      {if $orderPayPalTransactionStatus === 'DECLINED' || $orderPayPalTransactionStatus === 'FAILED' }
        <div class="alert alert-danger">
            {l s='Your payment has been declined by our payment gateway, please contact us via the link below.' mod='ps_checkout'}
        </div>
      {/if}

      {if $approvalLink && $orderPayPalStatus === 'PENDING_APPROVAL' }
        <div class="alert alert-warning">
            {l s='Your payment needs approving, please click the button below.' mod='ps_checkout'}
        </div>
        <div>
          <a class="btn btn-primary" href="{$approvalLink|escape:'html':'UTF-8'}">
              {l s='Approve payment' mod='ps_checkout'}
          </a>
            {l s='You will be redirected to an external secured page of our payment gateway.' mod='ps_checkout'}
        </div>
      {/if}

      {if $payerActionLink && $orderPayPalStatus === 'PAYER_ACTION_REQUIRED' }
        <div class="alert alert-info">
            {l s='Your payment needs to be authenticated, please click the button below.' mod='ps_checkout'}
        </div>
        <div>
          <a class="btn btn-primary" href="{$payerActionLink|escape:'html':'UTF-8'}">
              {l s='Verify payment' mod='ps_checkout'}
          </a>
            {l s='You will be redirected to an external secured page of our payment gateway.' mod='ps_checkout'}
        </div>
      {/if}
    <p>
      <a href="{$contactUsLink|escape:'html':'UTF-8'}" class="contact-us">
          {l s='If you have any question, please contact us.' mod='ps_checkout'}
      </a>
    </p>
  </section>
{else}
  <section id="ps_checkout-displayOrderDetail" class="box">
    <h3 class="page-subheading">
        {l s='Payment gateway information' mod='ps_checkout'}
    </h3>

      {if !$orderIsPaid}
        <div class="alert alert-warning">
            {l s='We have not received information about the successful payment of the order.' mod='ps_checkout'}
        </div>
      {/if}

    <div class="table-responsive">
      <table class="table table-striped table-condensed">
        <tr>
          <th style="width:30%; white-space: nowrap;">{l s='Funding source' mod='ps_checkout'}</th>
          <td>{$orderPayPalFundingSource|escape:'html':'UTF-8'}</td>
        </tr>
          {if $orderPayPalTransactionId}
            <tr>
              <th>{l s='Transaction identifier' mod='ps_checkout'}</th>
              <td>{$orderPayPalTransactionId|escape:'html':'UTF-8'}</td>
            </tr>
            <tr>
              <th>{l s='Transaction status' mod='ps_checkout'}</th>
              <td>{$orderPayPalTransactionStatus|escape:'html':'UTF-8'}</td>
            </tr>
          {/if}
      </table>
    </div>

      {if $orderPayPalTransactionStatus === 'DECLINED' || $orderPayPalTransactionStatus === 'FAILED'}
        <div class="alert alert-danger">
            {l s='Your payment has been declined by our payment gateway, please contact us via the link below.' mod='ps_checkout'}
        </div>
      {/if}

      {if $approvalLink && $orderPayPalStatus === 'PENDING_APPROVAL'}
        <div class="alert alert-info">
            {l s='Your payment needs approving, please click the button below.' mod='ps_checkout'}
        </div>
        <div class="submit">
          <a class="button btn btn-default button-medium" href="{$approvalLink|escape:'html':'UTF-8'}">
            <span>
                {l s='Approve payment' mod='ps_checkout'}
                <i class="icon-chevron-right right"></i>
            </span>
          </a>
          <span>{l s='You will be redirected to an external secured page of our payment gateway.' mod='ps_checkout'}</span>
        </div>
      {/if}

      {if $payerActionLink && $orderPayPalStatus === 'PAYER_ACTION_REQUIRED' }
        <div class="alert alert-info">
            {l s='Your payment needs to be authenticated, please click the button below.' mod='ps_checkout'}
        </div>
        <div class="submit">
          <a class="button btn btn-default button-medium" href="{$payerActionLink|escape:'html':'UTF-8'}">
            <span>
                {l s='Verify payment' mod='ps_checkout'}
                <i class="icon-chevron-right right"></i>
            </span>
          </a>
          <span>{l s='You will be redirected to an external secured page of our payment gateway.' mod='ps_checkout'}</span>
        </div>
      {/if}

    <p class="pack_content">
      <a href="{$contactUsLink|escape:'html':'UTF-8'}" class="contact-us">
          {l s='If you have any question, please contact us.' mod='ps_checkout'}
      </a>
    </p>
  </section>
{/if}
