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

<div class="card mt-2" id="ps_checkout">
  <div class="card-header">
    <h3 class="card-header-title">
      <img src="{$moduleLogoUri|escape:'html':'UTF-8'}" alt="{$moduleName|escape:'html':'UTF-8'}" width="20" height="20">
      {$moduleName|escape:'html':'UTF-8'}
    </h3>
  </div>
  <div class="card-body">
    <div class="paypal-order-notifications">
    </div>
    <div class="paypal-order-container">
    </div>
    <div class="paypal-order-loader text-center">
      <button class="btn-primary-reverse onclick unbind spinner"></button>
    </div>
  </div>
</div>

{include file='./partials/adminOrderView.tpl' legacy=false orderPrestaShopId=$orderPrestaShopId orderPayPalBaseUrl=$orderPayPalBaseUrl}
