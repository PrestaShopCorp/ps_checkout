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

<div class="additional-information">

    <div id="payments-sdk__contingency-lightbox"></div>

    <form id="hosted-fields-form">

        <div class="form-group row">
            <div class="col-md-8">
                <label for="card-number" class="form-control-label required">{l s='Card number' mod='ps_checkout'}</label>
                <div id="card-number" class="form-control">
                    <div id="card-image">
                        <i class="material-icons defautl-credit-card">credit_card</i>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-4">
                <label for="expiration-date" class="form-control-label required">{l s='Expiry date' mod='ps_checkout'}</label>
                <div id="expiration-date" class="form-control"></div>
            </div>
            <div class="col-md-4">
                <label for="cvv" class="form-control-label required">{l s='CVC' mod='ps_checkout'}</label>
                <div id="cvv" class="form-control"></div>
            </div>
        </div>

        {* Error when a payment has been refused by paypal
        eg : - CVV invalid
            - card not accepted
            - insufficient fund
        Acutally we cannot identify the error, paypal only return a 422.
        They are working on a new system that allow us to get relevant error message.
        For now -> just return a generic error messages
        *}
        {if $isCardPaymentError}
        <div class="alert alert-danger" role="alert" data-alert="danger">
          <p>
            {l s='There was an error during the payment. Please try again or contact the support.' mod='ps_checkout'}
          </p>
        </div>
        {/if}

    </form>


    {* Error returned by the paypal SDK
    The sdk make a first check on the card before trying to process the payment *}
    <div id="hostedFieldsErrors" class="hide-paypal-error">
        <div class="alert alert-danger" role="alert" data-alert="danger">
            <ul id="hostedFieldsErrorList">
            </ul>
        </div>
    </div>
</div>
