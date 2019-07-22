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
              <img class="mb-3" src="@/assets/images/paypal-logo.png" width="100">
              <p class="text-muted">
                {{ $t('panel.payment-acceptance.paypalLabel1') }}<br>
                {{ $t('panel.payment-acceptance.paypalLabel2') }}
              </p>
              <PaypalStatus />
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
              <CardStatus />
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
  import CardStatus from '@/components/block/card-status';
  import PaypalStatus from '@/components/block/paypal-status';
  import PSAlert from '@/components/form/alert';
  import {ALERT_TYPE_INFO} from '@/lib/alert';

  export default {
    components: {
      CardStatus,
      PaypalStatus,
      PSAlert,
    },
    computed: {
      ALERT_TYPE_INFO: () => ALERT_TYPE_INFO,
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
