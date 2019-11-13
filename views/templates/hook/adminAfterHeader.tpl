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

<div class="container">
    <div class="row">
        <div class="col">
            <div class="card">
                <h3 class="card-header">
                    <i class="material-icons">extension</i> {l s='One module, all payments methods.' mod='ps_checkout'}
                </h3>
                <div class="card-block">
                    <div class="module-item-list">
                        <div class="row module-item-wrapper-list py-3">
                            <div class="col-12 col-sm-2 col-md-2 col-lg-3">
                                <div class="img m-auto">
                                </div>
                            </div>
                            <div class="row col-12 col-sm-6 col-md-7 col-lg-7 pl-0">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-8 pl-0">
                                    <ul class="text-muted">
                                        <li class="mb-1">{l s='Offer the widest range of payment methods: cards, PayPal, etc.' mod='ps_checkout'}</li>
                                        <li class="mb-1">{l s='Benefit from all PayPal expertise and advantages' mod='ps_checkout'}</li>
                                        <li>{l s='Give access to relevant local payment methods for customers around the globe' mod='ps_checkout'}</li>
                                    </ul>
                                </div>
                                <div class="col-12 col-sm-12 col-md-12 col-lg-4 pl-0">
                                    <label class="text-muted">
                                        {l s='Including:' mod='ps_checkout'}
                                    </label>
                                    <div>
                                        <img class="payment-icon" src="{$imgPath|escape:'htmlall':'UTF-8'}paypal.jpg" alt="">
                                        <img class="payment-icon" src="{$imgPath|escape:'htmlall':'UTF-8'}visa.jpg" alt="">
                                        <img class="payment-icon" src="{$imgPath|escape:'htmlall':'UTF-8'}mastercard.jpg" alt="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-3 col-lg-2 mb-3 m-auto">
                                <div class="text-center">
                                    <a href="{$configureLink|escape:'htmlall':'UTF-8'}" class="btn btn-primary-reverse btn-outline-primary light-button">
                                        {l s='Configure' mod='ps_checkout'}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
