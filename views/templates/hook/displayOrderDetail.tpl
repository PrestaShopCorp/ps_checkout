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
        {$translations.blockTitle|escape:'html':'UTF-8'}
    </h3>

      {if $orderPayPalTransactionStatus === 'DECLINED' || $orderPayPalTransactionStatus === 'FAILED'}
        <div class="alert alert-danger">
            {$translations.notificationFailed|escape:'html':'UTF-8'}
        </div>
      {/if}

      {if $approvalLink && $orderPayPalStatus === 'PENDING_APPROVAL'}
        <div class="alert alert-warning">
            {$translations.notificationPendingApproval|escape:'html':'UTF-8'}
        </div>
      {/if}

      {if $payerActionLink && $orderPayPalStatus === 'PAYER_ACTION_REQUIRED'}
        <div class="alert alert-warning">
            {$translations.notificationPayerActionRequired|escape:'html':'UTF-8'}
        </div>
      {/if}

    <div class="definition-list">
      <dl>
        <dt>{$translations.fundingSource|escape:'html':'UTF-8'}</dt>
        <dd>{$orderPayPalFundingSourceTranslated|escape:'html':'UTF-8'}</dd>
          {if $orderPayPalTransactionId}
            <dt>{$translations.transactionIdentifier|escape:'html':'UTF-8'}</dt>
            <dd>{$orderPayPalTransactionId|escape:'html':'UTF-8'}</dd>
            <dt>{$translations.transactionStatus|escape:'html':'UTF-8'}</dt>
            <dd>{$orderPayPalTransactionStatusTranslated|escape:'html':'UTF-8'}</dd>
            <dt>{$translations.amountPaid|escape:'html':'UTF-8'}</dt>
            <dd>{$orderPayPalTransactionAmount|escape:'html':'UTF-8'}</dd>
          {else}
            <dt>{$translations.orderIdentifier|escape:'html':'UTF-8'}</dt>
            <dd>{$orderPayPalId|escape:'html':'UTF-8'}</dd>
            <dt>{$translations.orderStatus|escape:'html':'UTF-8'}</dt>
            <dd>{$orderPayPalStatus|escape:'html':'UTF-8'}</dd>
          {/if}
      </dl>
    </div>

      {if $approvalLink && $orderPayPalStatus === 'PENDING_APPROVAL'}
        <p>
          <a class="btn btn-primary" href="{$approvalLink|escape:'html':'UTF-8'}">
              {$translations.buttonApprove|escape:'html':'UTF-8'}
          </a>
            {$translations.externalRedirection|escape:'html':'UTF-8'}
        </p>
      {/if}

      {if $payerActionLink && $orderPayPalStatus === 'PAYER_ACTION_REQUIRED'}
        <p>
          <a class="btn btn-primary" href="{$payerActionLink|escape:'html':'UTF-8'}">
              {$translations.buttonPayerAction|escape:'html':'UTF-8'}
          </a>
            {$translations.externalRedirection|escape:'html':'UTF-8'}
        </p>
      {/if}
    <p>
      <a href="{$contactUsLink|escape:'html':'UTF-8'}" class="contact-us">
          {$translations.contactLink|escape:'html':'UTF-8'}
      </a>
    </p>
  </section>
{else}
  <section id="ps_checkout-displayOrderDetail" class="box">
    <h3 class="page-subheading">
        {$translations.blockTitle|escape:'html':'UTF-8'}
    </h3>

      {if $orderPayPalTransactionStatus === 'DECLINED' || $orderPayPalTransactionStatus === 'FAILED'}
        <div class="alert alert-danger">
            {$translations.notificationFailed|escape:'html':'UTF-8'}
        </div>
      {/if}

      {if $approvalLink && $orderPayPalStatus === 'PENDING_APPROVAL'}
        <div class="alert alert-warning">
            {$translations.notificationPendingApproval|escape:'html':'UTF-8'}
        </div>
      {/if}

      {if $payerActionLink && $orderPayPalStatus === 'PAYER_ACTION_REQUIRED'}
        <div class="alert alert-warning">
            {$translations.notificationPayerActionRequired|escape:'html':'UTF-8'}
        </div>
      {/if}

    <div class="table-responsive">
      <table class="table table-striped table-condensed">
        <tr>
          <th style="width:30%; white-space: nowrap;">{$translations.fundingSource|escape:'html':'UTF-8'}</th>
          <td>{$orderPayPalFundingSourceTranslated|escape:'html':'UTF-8'}</td>
        </tr>
          {if $orderPayPalTransactionId}
            <tr>
              <th>{$translations.transactionIdentifier|escape:'html':'UTF-8'}</th>
              <td>{$orderPayPalTransactionId|escape:'html':'UTF-8'}</td>
            </tr>
            <tr>
              <th>{$translations.transactionStatus|escape:'html':'UTF-8'}</th>
              <td>{$orderPayPalTransactionStatusTranslated|escape:'html':'UTF-8'}</td>
            </tr>
            <tr>
              <th>{$translations.amountPaid|escape:'html':'UTF-8'}</th>
              <td>{$orderPayPalTransactionAmount|escape:'html':'UTF-8'}</td>
            </tr>
          {/if}
      </table>
    </div>

      {if $approvalLink && $orderPayPalStatus === 'PENDING_APPROVAL'}
        <div class="submit">
          <a class="button btn btn-default button-medium" href="{$approvalLink|escape:'html':'UTF-8'}">
            <span>
                {$translations.buttonApprove|escape:'html':'UTF-8'}
                <i class="icon-chevron-right right"></i>
            </span>
          </a>
          <span>{$translations.externalRedirection|escape:'html':'UTF-8'}</span>
        </div>
      {/if}

      {if $payerActionLink && $orderPayPalStatus === 'PAYER_ACTION_REQUIRED'}
        <div class="submit">
          <a class="button btn btn-default button-medium" href="{$payerActionLink|escape:'html':'UTF-8'}">
            <span>
                {$translations.buttonPayerAction|escape:'html':'UTF-8'}
                <i class="icon-chevron-right right"></i>
            </span>
          </a>
          <span>{$translations.externalRedirection|escape:'html':'UTF-8'}</span>
        </div>
      {/if}

    <p class="pack_content">
      <a href="{$contactUsLink|escape:'html':'UTF-8'}" class="contact-us">
          {$translations.contactLink|escape:'html':'UTF-8'}
      </a>
    </p>
  </section>
{/if}
