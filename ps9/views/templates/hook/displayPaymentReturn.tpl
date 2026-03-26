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

<section id="ps_checkout-displayPaymentReturn">
    <div class="card border-1 mb-4">
        <div class="card-body">
            <h2 class="h2 mb-4">
                {$translations.blockTitle|escape:'html':'UTF-8'}
            </h2>

            <ul>
                <li>
                    {$translations.fundingSource|escape:'html':'UTF-8'}: {$orderPayPalFundingSourceTranslated|escape:'html':'UTF-8'}
                </li>
                {if $vault}
                    <li>{$translations.paymentMethodStatus|escape:'html':'UTF-8'}: {$tokenIdentifier|escape:'html':'UTF-8'}
                        {if $isTokenSaved}
                            {$translations.paymentTokenSaved|escape:'html':'UTF-8'}
                        {else}
                            {$translations.paymentTokenNotSaved|escape:'html':'UTF-8'}
                        {/if}
                    </li>
                {/if}
                {if $orderPayPalTransactionId}
                    <li>{$translations.transactionIdentifier|escape:'html':'UTF-8'}: {$orderPayPalTransactionId|escape:'html':'UTF-8'}</li>
                    <li>{$translations.transactionStatus|escape:'html':'UTF-8'}: {$orderPayPalTransactionStatusTranslated|escape:'html':'UTF-8'}</li>
                    <li>{$translations.amountPaid|escape:'html':'UTF-8'}: {$orderPayPalTransactionAmount|escape:'html':'UTF-8'}</li>
                {else}
                    <li>{$translations.orderIdentifier|escape:'html':'UTF-8'}: {$orderPayPalId|escape:'html':'UTF-8'}</li>
                    <li>{$translations.orderStatus|escape:'html':'UTF-8'}: {$orderPayPalStatus|escape:'html':'UTF-8'}</li>
                {/if}
            </ul>

            {if $approvalLink && $orderPayPalStatus === 'PENDING_APPROVAL'}
                <div class="alert alert-warning">
                    {$translations.notificationPendingApproval|escape:'html':'UTF-8'}
                </div>
                <p>
                    <a class="btn btn-primary" href="{$approvalLink|escape:'html':'UTF-8'}">
                        {$translations.buttonApprove|escape:'html':'UTF-8'}
                    </a>
                    {$translations.externalRedirection|escape:'html':'UTF-8'}
                </p>
            {/if}

            {if $payerActionLink && $orderPayPalStatus === 'PAYER_ACTION_REQUIRED' }
                <div class="alert alert-warning">
                    {$translations.notificationPayerActionRequired|escape:'html':'UTF-8'}
                </div>
                <p>
                    <a class="btn btn-primary" href="{$payerActionLink|escape:'html':'UTF-8'}">
                        {$translations.buttonPayerAction|escape:'html':'UTF-8'}
                    </a>
                    {$translations.externalRedirection|escape:'html':'UTF-8'}
                </p>
            {/if}

            <a href="{$contactUsLink|escape:'html':'UTF-8'}" class="contact-us">
                {$translations.contactLink|escape:'html':'UTF-8'}
            </a>
        </div>
    </div>
</section>