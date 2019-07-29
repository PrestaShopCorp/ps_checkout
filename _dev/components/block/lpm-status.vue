<template>
  <div>
    <label v-if="!onboardingIsCompleted" class="text-muted">
      {{ $t('block.payment-status.disabled') }}
    </label>
    <label v-else-if="!emailIsValid" class="text-warning">
      <i class="material-icons">error_outline</i> {{ $t('block.payment-status.approvalPending') }}
    </label>
    <label v-else-if="lpmIsActive" class="text-success">
      <i class="material-icons">check</i> {{ $t('block.payment-status.live') }}
    </label>
    <label v-else class="text-danger">
      <i class="material-icons">error_outline</i> {{ $t('block.payment-status.disabled') }}
    </label>
  </div>
</template>

<script>
  export default {
    name: 'PaypalStatus',
    computed: {
      onboardingIsCompleted() {
        return this.$store.state.paypal.onboardingCompleted
          && this.$store.state.firebase.onboardingCompleted;
      },
      emailIsValid() {
        return this.$store.state.paypal.emailIsValid;
      },
      lpmIsActive() {
        return this.$store.state.configuration.captureMode === 'CAPTURE'
          && this.$store.state.paypal.paypalIsActive;
      },
    },
  };
</script>
