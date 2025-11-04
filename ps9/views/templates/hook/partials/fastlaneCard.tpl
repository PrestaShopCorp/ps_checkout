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
<form id="ps_checkout-fastlane-card-form" class="form-horizontal loading">
  <div id="ps_checkout-card-fields-form-loader">
    <img src="{$modulePath}views/img/tail-spin.svg" alt="spin">
  </div>

  <div id="ps_checkout-fastlane-card">
    <div class="fastlane-card-info">
      <div class="fastlane-card-main">
        <div id="ps_checkout-fastlane-card-logo" class="ps-card-logo"></div>

        <div id="ps_checkout-fastlane-card-summary"></div>
      </div>

      <div id="fastlane-payment-watermark"></div>
    </div>

    <button id="payment-change-button" class="btn" type="button" >
      <i class="material-icons edit">mode_edit</i>
    </button>
  </div>
</form>
