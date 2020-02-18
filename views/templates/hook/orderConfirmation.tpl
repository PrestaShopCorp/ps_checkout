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

{if $status === 'ok'}
  {if $shopIs17 === false}
    <p class="alert alert-success">{l s='Your order on %s is complete.' sprintf=$shop_name mod='ps_checkout'}</p>
    <div class="box">
      {l s='If you have questions, comments or concerns, please contact our' mod='ps_checkout'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team' mod='ps_checkout'}</a>.
    </div>
  {/if}
{elseif $status === 'failed'}
  <div class="alert alert-warning">
    {l s='Your order hasn\'t been validated yet, only created. There can be an issue with your payment or it can be captured later, please contact our customer service to have more details about it.' mod='ps_checkout'}
  </div>
{/if}
