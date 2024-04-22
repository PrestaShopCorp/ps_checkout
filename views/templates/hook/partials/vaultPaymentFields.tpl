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
<div class="ps_checkout-vault-payment-container-{$paymentIdentifier} ps_checkout-vault-fields">
  <div>
    <label for="ps_checkout-vault-payment-{$paymentIdentifier}" class="ps_checkout-vault-label">
      <input type="checkbox" value="1" name="ps_checkout-vault-payment-{$paymentIdentifier}" id="ps_checkout-vault-payment-{$paymentIdentifier}">
      {l s='Securely store payment details for future purchases' mod='ps_checkout'}
      <img src="{$lockIcon}" alt="lock" width="15" height="15">
    </label>
  </div>
{*  <div>*}
{*    <label for="ps_checkout-favorite-payment-{$paymentIdentifier}" class="ps_checkout-vault-label">*}
{*      <input type="checkbox" disabled value="1" name="ps_checkout-favorite-payment-{$paymentIdentifier}" id="ps_checkout-favorite-payment-{$paymentIdentifier}">*}
{*      {if $paymentIdentifier=='card'}*}
{*        {l s='Make this card favorite / default ' mod='ps_checkout'}*}
{*      {else}*}
{*        {l s='Make this account favorite / default ' mod='ps_checkout'}*}
{*      {/if}*}
{*    </label>*}
{*  </div>*}
{*  <script>*}
{*    const vaultCheckbox{$paymentIdentifier} = document.getElementById('ps_checkout-vault-payment-{$paymentIdentifier}');*}
{*    const favoriteCheckbox{$paymentIdentifier} = document.getElementById('ps_checkout-favorite-payment-{$paymentIdentifier}');*}
{*    vaultCheckbox{$paymentIdentifier}.addEventListener('change', (event) => {*}
{*      if (!event.target.checked) {*}
{*        favoriteCheckbox{$paymentIdentifier}.checked = false;*}
{*      }*}
{*      favoriteCheckbox{$paymentIdentifier}.toggleAttribute('disabled', !event.target.checked);*}
{*    });*}
{*  </script>*}
</div>
