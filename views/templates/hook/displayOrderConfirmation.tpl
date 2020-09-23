{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
<div class="alert alert-success">
  {if $isAuthorized }
    {l s='Your order is confirmed.' mod='ps_checkout'}<br>
    {if $isShop17}
      <i class="material-icons">info</i>
    {/if}
     {l s="Important : you won't be charged until the order is shipping is effective." mod="ps_checkout"}
  {else}
    {l s='Your order has been created and is waiting for the payment to be approved by your bank, you will receive an email when the payment is accepted and effective.' mod='ps_checkout'}
  {/if}
</div>

