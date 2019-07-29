<template>
  <div>
    <label v-if="!onboardingIsCompleted" class="text-muted">
      {{ $t('block.payment-status.disabled') }}
    </label>
    <template v-else-if="paypalIsActive">
      <p v-if="displayLabels" class="text-muted">
        {{ $t('block.payment-status.paypalLabel') }}
      </p>
      <label class="text-success">
        <i class="material-icons">check</i> {{ $t('block.payment-status.live') }}
      </label>
    </template>
    <template v-else>
      <p v-if="displayLabels" class="text-muted">
        {{ $t('block.payment-status.paypalLabelEmailNotValid') }}
      </p>
      <label class="text-warning">
        <i class="material-icons">error_outline</i> {{ $t('block.payment-status.approvalPending') }}
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
        default: false,
      },
    },
    computed: {
      onboardingIsCompleted() {
        return this.$store.state.paypal.onboardingCompleted
          && this.$store.state.firebase.onboardingCompleted;
      },
      paypalIsActive() {
        return this.$store.state.paypal.paypalIsActive
          && this.$store.state.paypal.emailIsValid;
      },
    },
  };
</script>
