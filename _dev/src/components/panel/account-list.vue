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
  <form class="form form-horizontal">
    <div class="card">
      <h3 class="card-header">
        <i class="material-icons">settings</i> {{ $t('panel.account-list.accountSettings') }}
      </h3>
      <div class="card-body">

        <div class="d-flex">
          <div class="d-flex flex-grow-1">
            <img
              class="mr-2 align-self-center"
              src="@/assets/images/paypal-logo-thumbnail.png"
              width="46"
            >
            <div class="align-self-center">
              <template v-if="paypalStatusAccount === false">
                {{ $t('panel.account-list.activatePayment') }}
              </template>
              <template v-else>
                {{ $t('panel.account-list.accountIsLinked') }}<br>
                <b>{{ paypalEmail }}</b>
              </template>
            </div>
          </div>
          <div class="align-self-center">
            <div
              v-if="paypalStatusAccount === false"
            >
              <Onboarding />
            </div>
            <div
              class="text-right"
              v-else
            >
              <b-button
                variant="outline-secondary"
                @click.prevent="paypalUnlink()"
              >
                {{ $t('panel.account-list.useAnotherAccount') }}
              </b-button>
            </div>
          </div>
        </div>

        <b-alert
          v-if="onboardingLinkError"
          class="col-12"
          variant="danger"
          show
        >
          <p>{{ $t('panel.account-list.onboardingLinkError') }}</p>
        </b-alert>

        <div class="pt-4" v-if="firebaseStatusAccount && paypalStatusAccount">
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

            <b-alert
              v-if="cardPaymentIsActive === 'SUSPENDED'"
              variant="danger"
              show
            >
              <h2>{{ $t('pages.accounts.suspendedAlertTitle') }}</h2>
              <p>
                {{ $t('pages.accounts.suspendedAlertLabel') }}
              </p>
              <div class="mt-3">
                <a
                  href="https://www.paypal.com/uk/smarthelp/article/how-do-i-remove-the-limitation-from-my-account-faq2189"
                  target="_blank"
                >
                  {{ $t('pages.accounts.suspendedButton') }}
                  <i class="material-icons">arrow_right_alt</i>
                </a>
              </div>
            </b-alert>

            <b-alert
              v-if="cardPaymentIsActive === 'REVOKED'"
              variant="danger"
              show
            >
              <h2>{{ $t('pages.accounts.revokedAlertTitle') }}</h2>
              <p>
                {{ $t('pages.accounts.revokedAlertLabel') }}
              </p>
              <div class="mt-3">
                <a
                  href="https://www.paypal.com/businessmanage/account/accountAccess"
                  target="_blank"
                >
                  {{ $t('pages.accounts.revokedButton') }}
                  <i class="material-icons">arrow_right_alt</i>
                </a>
              </div>
            </b-alert>
          </template>
        </div>
      </div>
    </div>
  </form>
</template>

<script>
  import Onboarding from '@/components/block/onboarding';
  import {isOnboardingCompleted} from 'prestashop_accounts_vue_components';

  export default {
    components: {
      Onboarding,
    },
    computed: {
      onboardingLinkError() {
        return this.$store.state.paypal.paypalOnboardingLink === false;
      },
      isReady() {
        return this.$store.state.context.isReady;
      },
      paypalEmail() {
        return this.$store.state.paypal.emailMerchant;
      },
      firebaseStatusAccount() {
        return isOnboardingCompleted();
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
    methods: {
      paypalUnlink() {
        this.$store.dispatch('unlink').then(() => {
          // TODO: Where trigger the psx onboarding
          // this.$store.dispatch('psxOnboarding', false);
          this.$store.dispatch('getOnboardingLink');
        });
      },
    },
  };
</script>

<style scoped>
.nobootstrap .table {
  border: unset;
  border-radius: unset;
}
.nobootstrap .table tr:first-child td {
  border-top: 0 !important;
}

.nobootstrap .table,
.nobootstrap .table tr:last-child td
{
  border-bottom: 0 !important;
}

.line-separator {
  height:1px;
  opacity: 0.2;
  background:#6B868F;
  border-bottom: 2px solid #6B868F;
}
#app .modal {
  background: rgba(0,0,0,0.4);
}
#app .modal-content {
  border-radius: unset;
}
#app .modal-body {
  font-size: 14px;
}
#app #modalLogout .modal-dialog {
  top: 35%;
}
.fade-enter-active, .fade-leave-active {
 transition: opacity .2s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
 opacity: 0;
}
.fs-14 {
  font-size: 14px;
}
.flex-grow-1 {
  flex-grow: 1;
}
</style>
