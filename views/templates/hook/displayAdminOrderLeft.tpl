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

<div id="ps_checkout" class="panel">
  <div class="panel-heading">
    <img src="{$moduleLogoUri|escape:'html':'UTF-8'}" alt="{$moduleName|escape:'html':'UTF-8'}" width="15" height="15">
    {$moduleName|escape:'html':'UTF-8'}
  </div>
  <div class="paypal-order-notifications">
  </div>
  <div class="paypal-order-container">
  </div>
  <div class="paypal-order-loader">
    <i class="process-icon-loading"></i>
  </div>
</div>

{include file='./partials/adminOrderView.tpl' legacy=true orderPrestaShopId=$orderPrestaShopId orderPayPalBaseUrl=$orderPayPalBaseUrl}
