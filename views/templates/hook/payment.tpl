{**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{foreach from=$paymentOrder item=item key=key}

{if $item.name === 'card'}
  {if $cardIsActive}
  <p class="payment_module">
    <a href="{$link->getModuleLink('ps_checkout', 'PaymentCard16')|escape:'html'}" class="pscheckout-card" title="{l s='Pay by Card' mod='ps_checkout'}">
      <img src="{$path}payment-cards.png" alt="{l s='Pay by Card' mod='ps_checkout'}"/>
      {l s='Pay by Card' mod='ps_checkout'}
    </a>
  </p>
  {/if}
{else}
  {if $paypalIsActive}
  <p class="payment_module">
    <a href="{$link->getModuleLink('ps_checkout', 'PaymentPaypal16')|escape:'html'}" class="pscheckout-paypal" title="{l s='Pay with a PayPal account or other payment methods' mod='ps_checkout'}">
      <img src="{$path}paypal.png" alt="{l s='Pay with a PayPal account or other payment methods' mod='ps_checkout'}"/>
      {l s='Pay with a PayPal account or other payment methods' mod='ps_checkout'}
    </a>
  </p>
  {/if}
{/if}

{/foreach}



