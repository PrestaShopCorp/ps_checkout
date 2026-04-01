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
<div>
  <form id="ps_checkout-pay-upon-invoice-fields-form" class="form-horizontal loading">
    <div class="form-group">
      <label for="ps_checkout-pui-birthday" class="form-control-label required">
        {l s='Date of Birth' d='Modules.Checkout.Pscheckout'}
      </label>
      <input
              type="date"
              id="ps_checkout-pui-birthday"
              name="ps_checkout-pui-birthday"
              class="form-control"
              required="required"
              placeholder="YYYY-MM-DD"
              {if !empty($min_date)}min="{$min_date}"{/if}
              {if !empty($max_date)}max="{$max_date}"{/if}
              {if isset($customerBirthday) && $customerBirthday}value="{$customerBirthday|escape:'html':'UTF-8'}"{/if}
      />
    </div>
    <div class="form-group">
      <label for="ps_checkout-pui-phone" class="form-control-label required">
        {l s='Phone Number' d='Modules.Checkout.Pscheckout'}
      </label>
      <input
              type="tel"
              id="ps_checkout-pui-phone"
              name="ps_checkout-pui-phone"
              class="form-control"
              required="required"
              placeholder="+49 123 456789"
              {if isset($customerPhone) && $customerPhone}value="{$customerPhone|escape:'html':'UTF-8'}"{/if}
      />
    </div>
    <div id="paypal-legal-container"></div>
  </form>
</div>
