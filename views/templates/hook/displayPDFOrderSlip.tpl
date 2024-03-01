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

<table id="ps_checkout-tab" width="80%" style="padding: 4px; border: 1pt solid #000000;">
  <tr>
    <td class="right small grey bold" valign="middle" width="44%">{l s='Transaction reference' d='Shop.Pdf' pdf='true'}</td>
    <td class="center small white" width="56%">{$refund_id}</td>
  </tr>
  <tr>
    <td class="right small grey bold" valign="middle" width="44%">{l s='Amount' d='Shop.Pdf' pdf='true'}</td>
    <td class="center small white" width="56%">{displayPrice currency=$refund_currency_id price=$refund_amount}</td>
  </tr>
  <tr>
    <td class="right small grey bold" valign="middle" width="44%">{l s='Status' d='Shop.Pdf' pdf='true'}</td>
    <td class="center small white" width="56%">{$refund_status}</td>
  </tr>
</table>
