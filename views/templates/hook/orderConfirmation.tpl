{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2019 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $status === 'ok'}
    {* TODO: See with PO if we displat a message when the order is valid *}
    {* <div>
        {l s='Your order on' mod='ps_checkout'} <span class="bold">{$shop_name|escape:'htmlall':'UTF-8'}</span> {l s='is complete.' mod='ps_checkout'}
        <span class="bold">{l s='Your order will be sent as soon as possible.' mod='ps_checkout'}</span>
        {l s='For any questions or for further information, please contact our' mod='ps_checkout'}
    </div> *}
{elseif $status === 'failed'}
    <div class="alert alert-warning">
        {l s='Your order hasn\'t been validated yet, only created. There can be an issue with your payment or it can be captured later, please contact our customer service to have more details about it.' mod='ps_checkout'}
    </div>
{/if}
