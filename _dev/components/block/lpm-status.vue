<template>
  <div>
    <label v-if="!emailIsValid" class="text-warning">
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
      emailIsValid() {
        return this.$store.state.paypal.account.emailIsValid;
      },
      lpmIsActive() {
        return this.$store.state.configuration.module.captureMode === 'CAPTURE'
          && this.$store.state.paypal.account.paypalIsActive;
      },
    },
  };
</script>
