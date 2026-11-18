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
<div class="bootstrap">
  <div class="alert alert-{if isset($repairFailed) && $repairFailed}danger{else}warning{/if}" role="alert">
    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px 16px;">
      <div style="flex: 1 1 300px;">
        <p>
          <strong>{l s='The module installation is incomplete.' mod='ps_checkout'}</strong>
          {l s='Some components required by PrestaShop Checkout are missing, usually after a failed module reset or installation. Click Repair to restore them. Your existing settings and orders are kept.' mod='ps_checkout'}
        </p>
        {if isset($repairFailed) && $repairFailed}
          <p>{l s='The repair could not be completed. Check the module logs for details, then try again or contact support.' mod='ps_checkout'}</p>
        {/if}
      </div>
      <form method="post" style="margin: 0; flex-shrink: 0;">
        <button type="submit" name="submitPsCheckoutRepair" value="1" class="btn btn-primary">
          {l s='Repair' mod='ps_checkout'}
        </button>
      </form>
    </div>
  </div>
</div>

<style>
  /** Module configuration pages have a 1200px minimum width, which pushes the Repair button off small screens */
  #content.nobootstrap {
    min-width: 0;
  }
</style>
