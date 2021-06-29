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
<section id="ps_checkout-displayOrderConfirmation">
  {if $status === 'pending' }
    <div class="alert alert-warning">
      {l s='Your order is waiting for payment confirmation. You will receive an email when your payment has been validated. You can also check the order status in your order history in your account.' mod='ps_checkout'}
    </div>
  {elseif $isShop17 && $isAuthorized }
    {* PrestaShop 1.7 show a confirmation message itself, so we display only a message in case of isAuthorized *}
    <div class="alert alert-info">
      <i class="material-icons">info</i>
      {l s="Important : you won't be charged until the order's shipping is effective." mod="ps_checkout"}
    </div>
  {elseif !$isShop17 }
    {* PrestaShop 1.6 doesn't show a confirmation message itself, so have to display it *}
    <div class="alert alert-success">
      {l s='Your order is confirmed.' mod='ps_checkout'}
    </div>
    {if $isAuthorized }
      <div class="box">
        <p>{l s="Important : you won't be charged until the order's shipping is effective." mod="ps_checkout"}</p>
      </div>
    {/if}
  {/if}
</section>
