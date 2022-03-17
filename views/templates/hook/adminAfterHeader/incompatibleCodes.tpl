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

{if $incompatibleCodes}
    <div class="container">
        {if $isShop17}
            <div class="banner-alert">
                <div class="banner-icon">
                    <i class="material-icons">error_outline</i>
                </div>
        {else}
            <div class="alert alert-warning">
        {/if}

            <div class="banner-text">
                <h2>
                  {if $codesType === 'countries'}
                    {l s='PrestaShop Checkout transactions won\'t work in some of your configured countries, but there is a solution !' mod='ps_checkout'}
                  {else if $codesType === 'currencies'}
                    {l s='PrestaShop Checkout transactions won\'t work in some of your configured currencies, but there is a solution !' mod='ps_checkout'}
                  {/if}
                </h2>

                <p class="banner-upgrade-info">
                    {l s='Please upgrade your settings for :' mod='ps_checkout'}
                </p>

                <p class="incompatible-list">
                    <b><i>
                        {foreach $incompatibleCodes as $key => $incompatibleCode}
                            {$incompatibleCode}{if $key != count($incompatibleCodes) - 1},{/if}
                        {/foreach}
                    </b></i>
                </p>

                <a href="{$paymentPreferencesLink}" class="button-link" target="_blank">
                    {l s="Change {$codesType} activation for this payment module" mod='ps_checkout'}
                </a>

                <a class="btn btn-link banner-link" href="{$paypalLink}" target="_blank">
                    {l s='Know more about compliant ISO Codes' mod='ps_checkout'}

                    {if $isShop17}
                        <i class="material-icons banner-link-icon">trending_flat</i>
                    {else}
                        <i class="icon-long-arrow-right"></i>
                    {/if}
                </a>
            </div>
        </div>
    </div>
{/if}
