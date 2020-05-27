{**
 * 2007-2020 PrestaShop and Contributors
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
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{extends file='page.tpl'}

{block name='page_title'}
  {l s='Payment' d='Shop.Theme.Checkout'}
{/block}

{block name='page_content_top'}
  <div class="alert alert-{$alertClass|escape:'html':'UTF-8'}">
    <p><strong>{l s='There was an error processing your order' mod='ps_checkout'}</strong></p>
    <p>{l s='Customer Service have been notified, please contact us to check if your payment has been processed.' mod='ps_checkout'}</p>
    <p><a href="{$urls.pages.contact}" class="alert-link" id="link-customer-service"><i class="material-icons">message</i> {l s='Click here to contact Customer Service' mod='ps_checkout'}</a></p>
    <p>{l s='You can provide following additional details:' mod='ps_checkout'}</p>
    <ul>
      <li class="text-muted">{l s='Error code :' mod='ps_checkout'} {$exceptionCode|escape:'html':'UTF-8'}</li>
      <li class="text-muted">{l s='Error message :' mod='ps_checkout'} {$exceptionMessageForCustomer|escape:'html':'UTF-8'}</li>
    </ul>
  </div>
  <style>
    #module-ps_checkout-ValidateOrder .alert ul {
      list-style: inherit;
      padding-left: inherit;
      margin-bottom: auto;
    }
  </style>
  {widget name="contactform"}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const customerServiceLink = document.getElementById('link-customer-service');
      const contactForm = document.querySelector('.contact-form form');
      const contactFormOrderField = null !== contactForm ? contactForm.querySelector('[name="id_order"]') : null;
      const contactFormMessageField = null !== contactForm ? contactForm.querySelector('[name="message"]') : null;

      if (null !== contactForm) {
        contactForm.id = 'widget-contact-form';
        customerServiceLink.href = '#widget-contact-form';
      }

      if (null !== contactFormOrderField) {
        contactFormOrderField.readonly = true;
      }

      if (null !== contactFormMessageField) {
        let contactFormMessageValue = '\n';
        contactFormMessageValue += "{l s='Error code :' mod='ps_checkout'} {$exceptionCode|escape:'html':'UTF-8'}\n";
        contactFormMessageValue += "{l s='Error message :' mod='ps_checkout'} {$exceptionMessageForCustomer|escape:'html':'UTF-8'}";
        contactFormMessageField.value = contactFormMessageValue;
      }
    });
  </script>
{/block}

{block name='page_footer'}
  <a href="{$urls.pages.my_account}" class="account-link">
    <i class="material-icons">&#xE853;</i>
    <span>{l s='Your account' d='Shop.Theme.Customeraccount'}</span>
  </a>
  <a href="{$urls.pages.history}" class="account-link">
    <i class="material-icons">&#xE916;</i>
    <span>{l s='Order history and details' d='Shop.Theme.Customeraccount'}</span>
  </a>
  <a href="{$urls.pages.index}" class="account-link">
    <i class="material-icons">&#xE88A;</i>
    <span>{l s='Home' d='Shop.Theme.Global'}</span>
  </a>
{/block}
