<template>
  <div>
    <label v-if="paypalIsActive" class="text-success">
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
      paypalIsActive() {
        return this.$store.state.paypal.account.paypalIsActive
          && this.$store.state.paypal.account.emailIsValid;
      },
    },
  };
</script>
