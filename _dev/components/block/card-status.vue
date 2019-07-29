<template>
  <div>
    <template v-if="!onboardingIsCompleted">
      <label class="text-muted">
        {{ $t('block.payment-status.disabled') }}
      </label>
    </template>
    <template v-else-if="cardIsInReview">
      <p v-if="displayLabels" class="text-muted">
        {{ $t('block.payment-status.creditCardLabelPending') }}
      </p>
      <label class="text-warning">
        <i class="material-icons">error_outline</i> {{ $t('block.payment-status.approvalPending') }}
      </label>
    </template>
    <template v-else-if="cardStatus === 'SUBSCRIBED'">
      <p v-if="displayLabels" class="text-muted">
        {{ $t('block.payment-status.creditCardLabelLive') }}
      </p>
      <label class="text-success">
        <i class="material-icons">check</i> {{ $t('block.payment-status.live') }}
      </label>
    </template>
    <template v-else-if="cardStatus === 'LIMITED'">
      <p v-if="displayLabels" class="text-muted">
        {{ $t('block.payment-status.creditCardLabelLimited') }}
      </p>
      <label class="text-warning">
        <i class="material-icons">error_outline</i> {{ $t('block.payment-status.limited') }}
      </label>
    </template>
    <template v-else-if="cardStatus === 'DENIED'">
      <p v-if="displayLabels" class="text-muted">
        {{ $t('block.payment-status.creditCardLabelDenied') }}
      </p>
      <label class="text-danger">
        <i class="material-icons">error_outline</i> {{ $t('block.payment-status.denied') }}
      </label>
    </template>
  </div>
</template>

<script>
  export default {
    name: 'CardStatus',
    props: {
      displayLabels: {
        type: Boolean,
        required: false,
        default: false,
      },
    },
    computed: {
      onboardingIsCompleted() {
        return this.$store.state.paypal.onboardingCompleted
          && this.$store.state.firebase.onboardingCompleted;
      },
      cardIsInReview() {
        const {cardIsActive} = this.$store.state.paypal;
        if (cardIsActive === 'IN_REVIEW'
          || cardIsActive === 'NEED_MORE_DATA'
          || !this.$store.state.paypal.emailIsValid) {
          return true;
        }
        return false;
      },
      cardStatus() {
        return this.$store.state.paypal.cardIsActive;
      },
    },
  };
</script>
