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
    <label v-if="!onboardingIsCompleted" class="text-muted">
      {{ $t('block.payment-status.disabled') }}
    </label>
    <template v-else-if="paypalIsActive">
      <p v-if="displayLabels" class="fs-14">
        {{ $t('block.payment-status.paypalLabel') }}
      </p>
      <label v-else class="text-success">
        <i class="material-icons">check</i>
        {{ $t('block.payment-status.live') }}
      </label>
    </template>
    <template v-else>
      <p v-if="displayLabels" class="fs-14">
        {{ $t('block.payment-status.paypalLabelEmailNotValid') }}
      </p>
      <label v-else class="text-warning">
        <i class="material-icons">error_outline</i>
        {{ $t('block.payment-status.approvalPending') }}
      </label>
    </template>
  </div>
</template>

<script>
  export default {
    name: 'PaypalStatus',
    props: {
      displayLabels: {
        type: Boolean,
        required: false,
        default: false
      }
    },
    computed: {
      onboardingIsCompleted() {
        return (
          this.$store.state.paypal.onboardingCompleted &&
          this.$store.state.firebase.onboardingCompleted
        );
      },
      paypalIsActive() {
        return (
          this.$store.state.paypal.paypalIsActive &&
          this.$store.state.paypal.emailIsValid
        );
      }
    }
  };
</script>

<style scoped>
  .fs-14 {
    font-size: 14px;
  }
</style>
