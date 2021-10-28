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

<div id="ps-checkout-business-data-check" class="container">
    {if $isShop17}
    <style>
    button.ps-checkout {
      border: 0;
      margin-left: 5px !important;
      cursor: pointer;
    }

    button.ps-checkout a {
      text-decoration: none;
    }

    button.ps-checkout:focus,button.ps-checkout:hover{
        outline:none;
    }
    </style>
    <div class="banner-alert banner-alert-info">
        <div class="banner-icon banner-icon-info">
            <i class="material-icons">info</i>
        </div>
    {else}
    <div class="alert alert-info">
    {/if}
        <div class="banner-text">
            {l s="Prestashop Checkout: thank you for downloading the newest version, we need to ensure periodically that your business informations are up to date. You can review them by following the link below." mod='ps_checkout'}

            <br />

            {if 'isPayPalOnboarded' === $onboardingState}
                {l s="PrestaShop Checkout is still functional, only the module configuration section remains inaccessible without verifying your data." mod='ps_checkout'}
            {/if}

            <br /><br />

            <button class="ps-checkout button-link">
                <a href="{$configurationLink}" class="button-link">
                    {l s="Check your data" mod='ps_checkout'}
                </a>
            </button>

            <button id="dismiss-data-check" class="ps-checkout button-link ml-5">
                {l s='Close this message' mod='ps_checkout'}
            </button>
        </div>
    </div>

    <script>
    document.querySelector('#ps-checkout-business-data-check button#dismiss-data-check').onclick = function() {
      var req = new XMLHttpRequest();
      req.addEventListener('load', function () {
        document.querySelector('#ps-checkout-business-data-check').remove();
      });

      const form = new FormData();
      form.append('ajax', true);
      form.append('controller', 'AdminAjaxPrestashopCheckout');
      form.append('action', 'DismissBusinessDataCheck');

      req.open('POST', '{$ajaxController}');
      req.send(form);
    }
    </script>
</div>
