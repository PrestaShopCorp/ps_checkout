<template>
  <div>
    <div class="row">
      <div v-if="firebaseStatusAccount && paypalStatusAccount" class="container">
        <PSAlert v-if="!merchantEmailIsValid" :alert-type="ALERT_TYPE_WARNING">
          <h2>{{ $t('pages.accounts.approvalPending') }}</h2>
          <p>{{ $t('pages.accounts.waitingEmail') }}</p>
          <p class="text-muted my-1">{{ $t('pages.accounts.didntReceiveEmail') }}</p>
          <a class="btn btn-outline-secondary mt-1">{{ $t('pages.accounts.sendEmailAgain') }}</a>
        </PSAlert>
        <template v-else>
          <PSAlert v-if="cardPaymentIsActive === 'NEED_MORE_DATA'" :alert-type="ALERT_TYPE_WARNING">
            <h2>{{ $t('pages.accounts.documentNeeded') }}</h2>
            <p>{{ $t('pages.accounts.additionalDocumentsNeeded') }}</p>
            <ul class="my-1">
              <li><b>{{ $t('pages.accounts.photoIds') }}</b></li>
            </ul>
            <a class="btn btn-outline-secondary mt-1">{{ $t('pages.accounts.uploadFile') }}</a>
          </PSAlert>
          <PSAlert v-if="cardPaymentIsActive === 'IN_REVIEW' || cardPaymentIsActive === 'LIMITED'" :alert-type="ALERT_TYPE_WARNING">
            <h2>{{ $t('pages.accounts.undergoingCheck') }}</h2>
            <p>
              {{ $t('pages.accounts.severalDays') }}
              {{ $t('pages.accounts.youCanProcess') }} <b>{{ $t('pages.accounts.upTo') }}</b> {{ $t('pages.accounts.transactionsUntil') }}.
            </p>
            <div class="mt-3">
              <a href="#" target="_blank">
                {{ $t('pages.accounts.approvalPendingLink') }} <i class="material-icons">arrow_right_alt</i>
              </a>
            </div>
          </PSAlert>
          <PSAlert v-if="cardPaymentIsActive === 'DENIED'" :alert-type="ALERT_TYPE_DANGER">
            <h2>{{ $t('pages.accounts.accountDeclined') }}</h2>
            <p>
              {{ $t('pages.accounts.cannotProcessCreditCard') }}.
            </p>
            <div class="mt-3">
              <a href="#" target="_blank">
                {{ $t('pages.accounts.accountDeclinedLink') }} <i class="material-icons">arrow_right_alt</i>
              </a>
            </div>
          </PSAlert>
        </template>
      </div>
    </div>
    <div class="row">
      <div class="container">
        <AccountList />
      </div>
    </div>
    <div v-if="firebaseStatusAccount !== false && paypalStatusAccount !== false" class="row">
      <div class="container">
        <PaymentAcceptance />
      </div>
    </div>
    <div v-else class="row">
      <div class="container">
        <Reassurance />
      </div>
    </div>
  </div>
</template>

<script>
  import AccountList from '@/components/panel/account-list';
  import PaymentAcceptance from '@/components/panel/payment-acceptance';
  import Reassurance from '@/components/block/reassurance';
  import PSAlert from '@/components/form/alert';
  import {ALERT_TYPE_DANGER, ALERT_TYPE_WARNING} from '@/lib/alert';

  export default {
    name: 'Accounts',
    components: {
      AccountList,
      PaymentAcceptance,
      Reassurance,
      PSAlert,
    },
    computed: {
      firebaseStatusAccount() {
        return this.$store.state.firebase.account.onboardingCompleted;
      },
      paypalStatusAccount() {
        return this.$store.state.paypal.account.onboardingCompleted;
      },
      paypalPaymentIsActive() {
        return this.$store.state.paypal.account.paypalIsActive;
      },
      cardPaymentIsActive() {
        return this.$store.state.paypal.account.cardIsActive;
      },
      merchantEmailIsValid() {
        return this.$store.state.paypal.account.emailIsValid;
      },
      ALERT_TYPE_DANGER: () => ALERT_TYPE_DANGER,
      ALERT_TYPE_WARNING: () => ALERT_TYPE_WARNING,
    },
  };
</script>
