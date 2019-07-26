<template>
  <div>
    <label v-if="!onboardingIsCompleted" class="text-muted">
      {{ $t('block.payment-status.disabled') }}
    </label>
    <label v-else-if="paypalIsActive" class="text-success">
      <i class="material-icons">check</i> {{ $t('block.payment-status.live') }}
    </label>
    <label v-else class="text-warning">
      <i class="material-icons">error_outline</i> {{ $t('block.payment-status.approvalPending') }}
    </label>
  </div>
</template>

<script>
  export default {
    name: 'PaypalStatus',
    computed: {
      onboardingIsCompleted() {
        return this.$store.state.paypal.account.onboardingCompleted
          && this.$store.state.firebase.account.onboardingCompleted;
      },
      paypalIsActive() {
        return this.$store.state.paypal.account.paypalIsActive
          && this.$store.state.paypal.account.emailIsValid;
      },
    },
  };
</script>
