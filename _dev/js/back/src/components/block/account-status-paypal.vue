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
    <template v-if="!accountIslinked">
      <b-badge variant="info">
        {{ $t('pages.accounts.accountLinkingInProgress') }}
      </b-badge>
    </template>
    <template v-else-if="emailValidationNeeded">
      <b-badge variant="warning">
        {{ $t('pages.accounts.emailValidationNeeded') }}
      </b-badge>
    </template>
    <template v-else-if="approvalPending">
      <b-badge variant="warning">
        {{ $t('pages.accounts.approvalPending') }}
      </b-badge>
    </template>
    <template v-else>
      <b-badge variant="success">
        {{ $t('pages.accounts.approved') }}
      </b-badge>
    </template>
  </div>
</template>

<script>
  export default {
    name: 'AccountStatusPayPal',
    computed: {
      accountIslinked() {
        return this.$store.state.paypal.accountIslinked;
      },
      emailValidationNeeded() {
        return !this.$store.state.paypal.emailIsValid;
      },
      approvalPending() {
        return (
          !this.$store.state.paypal.paypalIsActive ||
          this.$store.state.paypal.cardIsActive === 'LIMITED' ||
          this.$store.state.paypal.cardIsActive === 'NEED_MORE_DATA' ||
          this.$store.state.paypal.cardIsActive === 'IN_REVIEW'
        );
      }
    }
  };
</script>
