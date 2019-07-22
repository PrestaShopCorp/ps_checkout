<template>
  <div>
    <label v-if="cardIsInReview" class="text-warning">
      <i class="material-icons">error_outline</i> {{ $t('block.payment-status.approvalPending') }}
    </label>
    <label v-else-if="cardStatus === 'SUBSCRIBED'" class="text-success">
      <i class="material-icons">check</i> {{ $t('block.payment-status.live') }}
    </label>
    <label v-else-if="cardStatus === 'LIMITED'" class="text-warning">
      <i class="material-icons">error_outline</i> {{ $t('block.payment-status.limited') }}
    </label>
    <label v-else-if="cardStatus === 'DENIED'" class="text-danger">
      <i class="material-icons">error_outline</i> {{ $t('block.payment-status.denied') }}
    </label>
  </div>
</template>

<script>
  export default {
    name: 'CardStatus',
    computed: {
      cardIsInReview() {
        const {cardIsActive} = this.$store.state.paypal.account;
        if (cardIsActive === 'IN_REVIEW'
          || cardIsActive === 'NEED_MORE_DATA'
          || !this.$store.state.paypal.account.emailIsValid) {
          return true;
        }
        return false;
      },
      cardStatus() {
        return this.$store.state.paypal.account.cardIsActive;
      },
    },
  };
</script>
