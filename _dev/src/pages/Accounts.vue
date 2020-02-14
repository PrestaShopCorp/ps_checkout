<!--**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *-->
<template>
  <div>
    <b-container v-if="firebaseStatusAccount && paypalStatusAccount">
      <b-alert
        v-if="!accountIslinked"
        variant="info"
        show
      >
        <h2>{{ $t('pages.accounts.waitingPaypalLinkingTitle') }}</h2>
        <p>{{ $t('pages.accounts.waitingPaypalLinking') }}</p>
      </b-alert>
      <b-alert
        v-else-if="!merchantEmailIsValid"
        variant="warning"
        show
      >
        <h2>{{ $t('pages.accounts.approvalPending') }}</h2>
        <p>{{ $t('pages.accounts.waitingEmail') }}</p>
        <p class="text-muted my-1">
          {{ $t('pages.accounts.didntReceiveEmail') }}
        </p>
        <p>
          <b-button
            href="https://www.paypal.com/businessprofile/settings"
            target="_blank"
            variant="outline-secondary"
          >
            {{ $t('pages.accounts.sendEmailAgain') }}
          </b-button>
        </p>
      </b-alert>
      <template v-else>
        <b-alert
          v-if="cardPaymentIsActive === 'NEED_MORE_DATA'"
          variant="warning"
          show
        >
          <h2>{{ $t('pages.accounts.documentNeeded') }}</h2>
          <p>{{ $t('pages.accounts.additionalDocumentsNeeded') }}</p>
          <ul class="my-1">
            <li><b>{{ $t('pages.accounts.photoIds') }}</b></li>
          </ul>
          <a
            href="https://www.paypal.com/policy/hub/kyc"
            target="_blank"
            class="btn btn-outline-secondary mt-1"
          >{{ $t('pages.accounts.knowMoreAboutAccount') }}</a>
        </b-alert>
        <b-alert
          v-if="cardPaymentIsActive === 'IN_REVIEW' || cardPaymentIsActive === 'LIMITED'"
          variant="warning"
          show
        >
          <h2>{{ $t('pages.accounts.undergoingCheck') }}</h2>
          <p>
            {{ $t('pages.accounts.severalDays') }}
            {{ $t('pages.accounts.youCanProcess') }}
            <b>{{ $t('pages.accounts.upTo') }}</b>
            {{ $t('pages.accounts.transactionsUntil') }}.
          </p>
          <div class="mt-3">
            <a
              href="https://www.paypal.com/policy/hub/kyc"
              target="_blank"
            >
              {{ $t('pages.accounts.knowMoreAboutAccount') }}
              <i class="material-icons">arrow_right_alt</i>
            </a>
          </div>
        </b-alert>
        <b-alert
          v-if="cardPaymentIsActive === 'DENIED'"
          variant="danger"
          show
        >
          <h2>{{ $t('pages.accounts.accountDeclined') }}</h2>
          <p>
            {{ $t('pages.accounts.cannotProcessCreditCard') }}.
          </p>
          <div class="mt-3">
            <a
              href="https://www.paypal.com/mep/dashboard"
              target="_blank"
            >
              {{ $t('pages.accounts.accountDeclinedLink') }}
              <i class="material-icons">arrow_right_alt</i>
            </a>
          </div>
        </b-alert>
      </template>
    </b-container>

    <b-container>
      <AccountList />
    </b-container>

    <b-container
      class="mt-4"
      v-if="firebaseStatusAccount !== false && paypalStatusAccount !== false"
    >
      <PaymentAcceptance />
    </b-container>

    <b-container
      class="mt-4"
      v-else
    >
      <Reassurance />
    </b-container>
  </div>
</template>

<script>
  import AccountList from '@/components/panel/account-list';
  import PaymentAcceptance from '@/components/panel/payment-acceptance';
  import Reassurance from '@/components/block/reassurance';

  export default {
    name: 'Accounts',
    components: {
      AccountList,
      PaymentAcceptance,
      Reassurance,
    },
    computed: {
      firebaseStatusAccount() {
        return this.$store.state.firebase.onboardingCompleted;
      },
      paypalStatusAccount() {
        return this.$store.state.paypal.onboardingCompleted;
      },
      paypalPaymentIsActive() {
        return this.$store.state.paypal.paypalIsActive;
      },
      cardPaymentIsActive() {
        return this.$store.state.paypal.cardIsActive;
      },
      merchantEmailIsValid() {
        return this.$store.state.paypal.emailIsValid;
      },
      accountIslinked() {
        return this.$store.state.paypal.accountIslinked;
      },
    },
  };
</script>
