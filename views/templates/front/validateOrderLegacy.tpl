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

{capture name="path"}
  <a href="{$link->getPageLink('cart', true)|escape:'html':'UTF-8'}">
    {l s='Your shopping cart'}
  </a>
  <span class="navigation-pipe">{$navigationPipe}</span>
  <span class="navigation_page">{l s='Order processing error' mod='ps_checkout'}</span>
{/capture}

<div id="validate-order-container">
  <div class="alert alert-{$alertClass|escape:'html':'UTF-8'}">
    <p><strong>{l s='There was an error processing your order' mod='ps_checkout'}</strong></p>
  </div>

  <p>{l s='Customer Service have been notified, please contact us to check if your payment has been processed.' mod='ps_checkout'}</p>
  <p><a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}" class="alert-link" id="link-customer-service"><i class="icon-envelope"></i> {l s='Click here to contact Customer Service' mod='ps_checkout'}</a></p>
  <p>{l s='You can provide following additional details:' mod='ps_checkout'}</p>
  <ul>
    <li class="text-muted">{l s='Error code :' mod='ps_checkout'} {$exceptionCode|escape:'html':'UTF-8'}</li>
    <li class="text-muted">{l s='Error message :' mod='ps_checkout'} {$exceptionMessageForCustomer|escape:'html':'UTF-8'}</li>
  </ul>
</div>

<ul class="footer_links clearfix">
  <li>
    <a class="btn btn-default button button-small" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
			<span>
				<i class="icon-user"></i> {l s='My account'}
			</span>
    </a>
  </li>
  <li>
    <a class="btn btn-default button button-small" href="{$link->getPageLink('history', true)|escape:'html':'UTF-8'}">
			<span>
				<i class="icon-list-ol"></i> {l s='Order history'}
			</span>
    </a>
  </li>
  <li>
    <a class="btn btn-default button button-small" href="{$link->getPageLink('index', true)|escape:'html':'UTF-8'}">
      <span><i class="icon-home"></i> {l s='Home'}</span>
    </a>
  </li>
</ul>

<style>
  #validate-order-container {
    margin-bottom: 10px;
  }
  #validate-order-container ul {
    list-style: inherit;
    margin-left: 20px;
    margin-bottom: auto;
  }
</style>
