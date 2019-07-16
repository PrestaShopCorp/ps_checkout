<template>
  <form class="form form-horizontal">
    <div class="card">
      <h3 class="card-header">
        <i class="material-icons">credit_card</i> {{ $t('panel.payment-acceptance.paymentAcceptanceTitle') }}
      </h3>
      <div class="card-block row">
        <div class="card-text">
          <div class="row">
            <div class="pl-0">
              <h2>PayPal</h2>
              <p class="text-muted">
                {{ $t('panel.payment-acceptance.paypalLabel1') }}<br>
                {{ $t('panel.payment-acceptance.paypalLabel2') }}
              </p>
              <label v-if="paypalIsActive" class="text-success">
                <i class="material-icons">check</i> {{ $t('panel.payment-acceptance.live') }}
              </label>
              <label v-else class="text-warning">
                <i class="material-icons">error_outline</i> {{ $t('panel.payment-acceptance.approvalPending') }}
              </label>
            </div>
          </div>
          <div class="row d-block">
            <div class="line-separator my-3" />
          </div>
          <div class="row">
            <div class="pl-0">
              <h2>{{ $t('panel.payment-acceptance.creditCardsLabel') }}</h2>
              <p class="text-muted">
                {{ $t('panel.payment-acceptance.creditCardLabel') }}
              </p>
              <label v-if="cardIsActive === 'SUBSCRIBED'" class="text-success">
                <i class="material-icons">check</i> {{ $t('panel.payment-acceptance.live') }}
              </label>
              <label v-else-if="cardIsActive === 'LIMITED'" class="text-warning">
                <i class="material-icons">error_outline</i> {{ $t('panel.payment-acceptance.limited') }}
              </label>
              <label v-else-if="cardIsActive === 'IN_REVIEW' || cardIsActive === 'NEED_MORE_DATA'" class="text-warning">
                <i class="material-icons">error_outline</i> {{ $t('panel.payment-acceptance.approvalPending') }}
              </label>
              <label v-else-if="cardIsActive === 'DENIED'" class="text-danger">
                <i class="material-icons">error_outline</i> {{ $t('panel.payment-acceptance.denied') }}
              </label>
            </div>
          </div>
          <div class="row mt-2">
            <PSAlert :alert-type="ALERT_TYPE_INFO">
              <h2>{{ $t('panel.payment-acceptance.tips') }}</h2>
              <p>{{ $t('panel.payment-acceptance.alertInfo') }}</p>
            </PSAlert>
          </div>
        </div>
      </div>
    </div>
  </form>
</template>

<script>
  import PSAlert from '@/components/form/alert';
  import {ALERT_TYPE_INFO} from '@/lib/alert';

  export default {
    components: {
      PSAlert,
    },
    data() {
      return {
        paypalIsLoaded: false,
      };
    },
    computed: {
      cardIsActive() {
        return this.$store.state.paypal.account.cardIsActive;
      },
      paypalIsActive() {
        return this.$store.state.paypal.account.paypalIsActive;
      },
      emailIsValid() {
        return this.$store.state.paypal.account.emailIsValid;
      },
      ALERT_TYPE_INFO: () => ALERT_TYPE_INFO,
    },
    methods: {
      firebaseLogout() {
        this.$store.dispatch('logout');
      },
      paypalUnlink() {
        this.$store.dispatch('unlink');
      },
    },
  };
</script>

<style scoped>
.line-separator {
  height:1px;
  opacity: 0.2;
  background:#6B868F;
  border-bottom: 2px solid #6B868F;
}
</style>
