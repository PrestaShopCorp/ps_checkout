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


<form id="conditions-to-approve-paypal" method="GET">
    <ul>
        <li>
        <div class="float-xs-left">
            <span class="custom-checkbox">
            <input id="conditions_to_approve-paypal" name="conditions_to_approve" required="" type="checkbox" value="1" class="ps-shown-by-js buttons-approve">
            <span><i class="material-icons rtl-no-flip checkbox-checked"></i></span>
            </span>
        </div>
        <div class="condition-label">
            <label class="js-terms paypal-label" for="conditions_to_approve-paypal">
            {l s='I agree to the' mod='ps_checkout'} <a href="" id="cta-terms-and-conditions-checkout">{l s='terms of service' mod='ps_checkout'}</a> {l s='and will adhere to them unconditionally' mod='ps_checkout'}.
            </label>
        </div>
        </li>
    </ul>
</form>

<div class="paypal-tips">{l s='You will be redirected to the related gateway to complete payment' mod='ps_checkout'}</div>

<div id="paypal-button-container"></div>

<div id="paypal-approve-error" class="hide-paypal-error">
    <article class="alert alert-danger" role="alert" data-alert="danger">
        <ul>
            <li>{l s='Please indicate that you have read Terms & Conditions and accept all terms' mod='ps_checkout'}</li>.
        </ul>
    </article>
</div>
