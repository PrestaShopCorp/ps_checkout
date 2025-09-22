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
<form id="ps_checkout-vault-token-form-{$paymentIdentifier}" class="form-horizontal ps_checkout-vault-token-form">
  <div>
    <span class="ps_checkout-token-explanation">
      {if $isFavorite}
        {l s='This payment method has been saved to your account and defined as favorite for future purchases.' d='Modules.Checkout.Pscheckout'}
      {else}
        {l s='This payment method has been saved to your account.' d='Modules.Checkout.Pscheckout'}
      {/if}
    </span>
  </div>
  <div>
    <button type="button" id="delete-{$paymentIdentifier}" class="ps_checkout-vault-token-delete">{l s='Delete' d='Modules.Checkout.Pscheckout'}</button>
  </div>
  <input type="hidden" name="ps_checkout-funding-source-{$paymentIdentifier}" value="{$fundingSource}">
  <input type="hidden" name="ps_checkout-vault-id-{$paymentIdentifier}" value="{$vaultId}">
  <input type="hidden" name="ps_checkout-vault-label-{$paymentIdentifier}" value="{$label}">
</form>
