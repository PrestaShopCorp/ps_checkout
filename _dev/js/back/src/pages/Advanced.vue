<!--**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *-->
<template>
  <div>
    <b-container class="mb-4">
      <CbInlineActivate />
    </b-container>
    <b-container>
      <AccountSettingsDeepLink
        class="mb-4 w-50 d-inline-flex"
        :title="$t('account-settings-deeplink.fraud-tool.title')"
        :description="$t('account-settings-deeplink.fraud-tool.description')"
        :linkTitle="$t('account-settings-deeplink.fraud-tool.link-title')"
        linkUrl="https://www.paypal.com/fraud-protection"
        icon="sports"
        :iconTitle="$t('account-settings-deeplink.fraud-tool.icon-title')"
      />
      <AccountSettingsDeepLink
        class="mb-4 w-50 d-inline-flex float-right"
        :title="$t('account-settings-deeplink.bank-account.title')"
        :description="$t('account-settings-deeplink.bank-account.description')"
        :linkTitle="$t('account-settings-deeplink.bank-account.link-title')"
        linkUrl="https://www.paypal.com/businessmanage/account/money"
        icon="account_balance"
        :iconTitle="$t('account-settings-deeplink.bank-account.icon-title')"
      />
      <AccountSettingsDeepLink
        class="mb-4 w-50 d-inline-flex"
        :title="$t('account-settings-deeplink.currencies.title')"
        :description="$t('account-settings-deeplink.currencies.description')"
        :linkTitle="$t('account-settings-deeplink.currencies.link-title')"
        linkUrl="https://www.paypal.com/businessmanage/account/money"
        icon="euro"
        :iconTitle="$t('account-settings-deeplink.currencies.icon-title')"
      />
      <AccountSettingsDeepLink
        class="mb-4 w-50 d-inline-flex float-right"
        :title="$t('account-settings-deeplink.conversion-rules.title')"
        :description="
          $t('account-settings-deeplink.conversion-rules.description')
        "
        :linkTitle="$t('account-settings-deeplink.conversion-rules.link-title')"
        linkUrl="https://www.paypal.com/businessmanage/preferences/payments"
        icon="toll"
        :iconTitle="$t('account-settings-deeplink.conversion-rules.icon-title')"
      />
    </b-container>
  </div>
</template>

<script>
  import AccountSettingsDeepLink from '@/components/block/account-settings-deeplink';
  import CbInlineActivate from '@/components/block/cb-inline-activate';

  export default {
    name: 'Advanced',
    components: {
      CbInlineActivate,
      AccountSettingsDeepLink
    }
  };
</script>

<style scoped>
  #app .d-inline-flex.w-50 {
    width: 49% !important;
  }
</style>
