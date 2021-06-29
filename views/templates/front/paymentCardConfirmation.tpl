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

<link rel="preload" href="{$paypalSdkLink|escape:'javascript':'UTF-8'|replace:'&amp;':'&'}" as="script">

{capture name=path}
<a href="{$link->getPageLink('order', true, NULL, 'step=3')|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='ps_checkout'}">
  {l s='Checkout' mod='ps_checkout'}
</a>
<span class="navigation-pipe">
  {$navigationPipe}
</span>
{l s='Card payment' mod='ps_checkout'}
{/capture}

<h1 class="page-heading">
  {l s='Order summary' mod='ps_checkout'}
</h1>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
<p class="alert alert-warning">
  {l s='Your shopping cart is empty.' mod='ps_checkout'}
</p>
{else}
<form method="post">
  <div class="box">
    <h3 class="page-subheading">
      {l s='Card' mod='ps_checkout'}
    </h3>
    <p>
      <strong class="dark">
        {l s='You have chosen to pay by Card.' mod='ps_checkout'} {l s='Here is a short summary of your order:' mod='ps_checkout'}
      </strong>
    </p>
    <p>
      - {l s='The total amount of your order comes to' mod='ps_checkout'}
      <b><span id="amount" class="price">{displayPrice price=$total}</span></b>
      {if $use_taxes == 1}
      {l s='(tax incl.)' mod='ps_checkout'}
      {/if}
    </p>
    <p>
      - {l s='Please confirm your order by clicking "I confirm my order".' mod='ps_checkout'}
    </p>

    <br>

    <div>
      <div id="payments-sdk__contingency-lightbox"></div>

      <form id="hosted-fields-form">

          <div class="form-group row">
              <div class="col-sm-4 col-md-4 col-lg-4">
                  <label for="card-number" class="form-control-label required">{l s='Card number' mod='ps_checkout'}</label>
                  <div id="card-number" class="form-control">
                      <div id="card-image">
                          <img class="defautl-credit-card" src="{$modulePath}views/img/credit_card.png" alt="">
                      </div>
                  </div>
              </div>
          </div>

          <div class="form-group row">
              <div class="col-sm-2 col-md-2 col-lg-2">
                  <label for="expiration-date" class="form-control-label required">{l s='Expiry date' mod='ps_checkout'}</label>
                  <div id="expiration-date" class="form-control"></div>
              </div>
              <div class="col-sm-2 col-md-2 col-lg-2">
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
      <div id="hostedFieldsErrors" class="alert alert-danger hide-paypal-error"></div>
    </div>
  </div><!-- .cheque-box -->
  <div class="cart_navigation clearfix" id="cart_navigation">
    <div class="flex-display">
      <a class="button-exclusive btn btn-default" href="{$link->getPageLink('order', true, NULL, 'step=3')|escape:'html':'UTF-8'}">
        <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='ps_checkout'}
      </a>
    </div>
    <button id="hosted-fields-validation" class="button btn btn-default button-medium" type="submit">
      <span>{l s='I confirm my order' mod='ps_checkout'}<i class="icon-chevron-right right"></i></span>
    </button>
  </div>
</form>
{/if}

<script>
  const cardNumberPlaceholder = "{l s='Card number' mod='ps_checkout'}";
  const expDatePlaceholder = "{l s='MM/YY' mod='ps_checkout'}";
  const cvvPlaceholder = "{l s='XXX' mod='ps_checkout'}";
  const paypalOrderId = "{$paypalOrderId|escape:'javascript':'UTF-8'}";
  const validateOrderLinkByCard = "{$validateOrderLinkByCard|escape:'javascript':'UTF-8'|replace:'&amp;':'&' nofilter}";
  const hostedFieldsErrors = {$hostedFieldsErrors|escape:'javascript':'UTF-8'|stripslashes|replace:'&amp;':'&' nofilter};

  /**
   * Create paypal script
   */
  function initPaypalScript() {
    if (typeof paypalSdkPsCheckout !== 'undefined') {
      return;
    }

    let psCheckoutScript = document.getElementById('paypalSdkPsCheckout');

    if (null !== psCheckoutScript) {
      return;
    }

    const paypalScript = document.createElement('script');
    paypalScript.setAttribute('src', "{$paypalSdkLink|escape:'javascript':'UTF-8'|replace:'&amp;':'&' nofilter}");
    paypalScript.setAttribute('data-client-token', "{$clientToken|escape:'javascript':'UTF-8'}");
    paypalScript.setAttribute('id', 'psCheckoutPaypalSdk');
    paypalScript.setAttribute('data-namespace', 'paypalSdkPsCheckout');
    paypalScript.setAttribute('data-enable-3ds', '');
    paypalScript.setAttribute('async', '');
    document.head.appendChild(paypalScript);
  }

  initPaypalScript();
</script>
