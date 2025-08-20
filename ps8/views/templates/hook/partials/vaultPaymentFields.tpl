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
<div class="ps_checkout-vault-payment-container-{$paymentIdentifier} ps_checkout-vault-fields">
  <div>
    <label for="ps_checkout-vault-payment-{$paymentIdentifier}" class="ps_checkout-vault-label">
      <input type="checkbox" value="1" name="ps_checkout-vault-payment-{$paymentIdentifier}" id="ps_checkout-vault-payment-{$paymentIdentifier}">
      {l s='Securely store payment details for future purchases' d='Modules.Checkout.Pscheckout'}
      <img src="{$modulePath}views/img/icons/lock_fill.svg" alt="lock" width="15" height="15">
    </label>
  </div>
</div>
