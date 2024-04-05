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

{**
 * WARNING
 *
 * This file allow only html
 *
 * It will be parsed by PrestaShop Core with PrestaShop\PrestaShop\Core\Payment\PaymentOptionFormDecorator
 *
 * Script tags will be removed and some HTML5 element can cause an Exception due to DOMDocument class
 *}
<form id="ps_checkout-vault-token-form-{$paymentIdentifier}" class="form-horizontal ps_checkout-vault-token-form">
  <div class="col-xs-12">
    <a id="delete-{$paymentIdentifier}" class="ps_checkout-vault-token-delete">{l s='Delete' mod='ps_checkout'}</a>
  </div>
  <div class="col-xs-12">
    {if !$isFavorite}
      <label for="ps_checkout-favorite-payment-{$paymentIdentifier}">
        <input type="checkbox" value="1" name="ps_checkout-favorite-payment-{$paymentIdentifier}" id="ps_checkout-favorite-payment-{$paymentIdentifier}">
        {l s='Make this my preferred payment method' mod='ps_checkout'}
      </label>
    {/if}
    <input type="hidden" name="ps_checkout-funding-source-{$paymentIdentifier}" value="{$fundingSource}">
    <input type="hidden" name="ps_checkout-vault-id-{$paymentIdentifier}" value="{$vaultId}">
    <input type="hidden" name="ps_checkout-vault-label-{$paymentIdentifier}" value="{$label}">
  </div>
</form>