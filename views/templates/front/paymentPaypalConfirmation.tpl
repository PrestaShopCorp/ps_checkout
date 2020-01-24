{**
 * 2007-2019 PrestaShop and Contributors
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
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{capture name=path}
<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='ps_checkout'}">{l s='Checkout' mod='ps_checkout'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='PayPal payment' mod='ps_checkout'}
{/capture}

<h1 class="page-heading">
  {l s='Order summary' mod='ps_checkout'}
</h1>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
<p class="alert alert-warning">
  {l s='Your shopping cart is empty.' mod='ps_checkout'}
</p>
{else}
<form action="{$link->getModuleLink('bankwire', 'validation', [], true)|escape:'html':'UTF-8'}" method="post">
  <div class="box">
    <h3 class="page-subheading">
      {l s='PayPal payment' mod='ps_checkout'}
    </h3>
    <p class="cheque-indent">
      <strong class="dark">
        {l s='You have chosen to pay by bank wire.' mod='ps_checkout'} {l s='Here is a short summary of your order:' mod='ps_checkout'}
      </strong>
    </p>
    <p>
      - {l s='The total amount of your order is' mod='ps_checkout'}
      <span id="amount" class="price">{displayPrice price=$total}</span>
      {if $use_taxes == 1}
      {l s='(tax incl.)' mod='ps_checkout'}
      {/if}
    </p>
    <p>
      - {l s='Bank wire account information will be displayed on the next page.' mod='ps_checkout'}
      <br />
      - {l s='Please confirm your order by clicking "I confirm my order".' mod='ps_checkout'}
    </p>
  </div><!-- .cheque-box -->
  <p class="cart_navigation clearfix" id="cart_navigation">
    <a class="button-exclusive btn btn-default" href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}">
      <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='ps_checkout'}
    </a>
    <button class="button btn btn-default button-medium" type="submit">
      <span>{l s='I confirm my order' mod='ps_checkout'}<i class="icon-chevron-right right"></i></span>
    </button>
  </p>
</form>
{/if}
