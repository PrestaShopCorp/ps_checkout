<!--**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *-->
<template>
  <form class="form form-horizontal">
    <div class="card">
      <h3 class="card-header">
        <b>{{ $t('panel.accounts.paypal.title') }}</b>
      </h3>

      <div class="card-body">
        <div class="d-flex align-items-center">
          <img
            src="@/assets/images/paypal-logo-thumbnail.png"
            alt="PayPal"
            class="ml-1"
          />

          <p class="text-muted ml-3 mr-auto">
            <template v-if="!paypalAccountStatus">
              {{ $t('panel.accounts.paypal.activate') }}
            </template>

            <template v-else>
              {{ $t('panel.accounts.paypal.isLinked') }}

              <br />

              <b>{{ paypalEmail }}</b>
            </template>
          </p>

          <AccountStatusPayPal v-if="paypalAccountStatus" class="mr-3" />

          <div class="text-center float-right" v-if="!paypalAccountStatus">
            <Onboarding />
          </div>

          <div class="text-right" v-else>
            <b-button
              variant="outline-secondary"
              @click.prevent="paypalUnlink()"
            >
              {{ $t('panel.accounts.paypal.useAnotherAccount') }}
            </b-button>
          </div>
        </div>

        <b-alert
          v-if="onboardingLinkError"
          class="col-12"
          variant="danger"
          show
        >
          <p>{{ $t('panel.accounts.paypal.onboardingLinkError') }}</p>
        </b-alert>

        <b-container v-if="checkoutAccountStatus && paypalAccountStatus">
          <b-alert v-if="!accountIslinked" variant="info" show>
            <h2>{{ $t('pages.accounts.waitingPaypalLinkingTitle') }}</h2>
            <p>{{ $t('pages.accounts.waitingPaypalLinking') }}</p>
          </b-alert>

          <b-alert v-else-if="!merchantEmailIsValid" variant="warning" show>
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
              v-if="cardPaymentIsActive === 'DENIED'"
              variant="danger"
              show
            >
              <h2>{{ $t('pages.accounts.accountDeclined') }}</h2>
              <p>{{ $t('pages.accounts.cannotProcessCreditCard') }}.</p>
              <div class="mt-3">
                <a href="https://www.paypal.com/mep/dashboard" target="_blank">
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
        </b-container>
      </div>
    </div>
  </form>
</template>

<script>
  import * as Sentry from '@sentry/vue';

  import AccountStatusPayPal from '@/components/block/account-status-paypal.vue';
  import Onboarding from '@/components/block/onboarding';
  import { PaypalAccountFalseOnboardingException } from '@/exception/paypal-onboarding.exception';

  export default {
    components: {
      AccountStatusPayPal,
      Onboarding
    },
    props: ['sendTrack'],
    computed: {
      onboardingLinkError() {
        return this.$store.state.paypal.paypalOnboardingLink === false;
      },
      paypalEmail() {
        const emailMerchant = this.$store.state.paypal.emailMerchant;
        if (
          !this.$store.state.context.liveStepViewed &&
          this.paypalAccountStatus
        ) {
          if (!emailMerchant) {
            Sentry.captureException(
              new PaypalAccountFalseOnboardingException()
            );
          }

          this.$store.dispatch('updatePaypalStatusViewed');
        }

        return emailMerchant;
      },
      checkoutAccountStatus() {
        return this.$store.state.firebase.onboardingCompleted;
      },
      paypalAccountStatus() {
        return this.$store.state.paypal.onboardingCompleted;
      },
      cardPaymentIsActive() {
        return this.$store.state.paypal.cardIsActive;
      },
      merchantEmailIsValid() {
        return this.$store.state.paypal.emailIsValid;
      },
      accountIslinked() {
        return this.$store.state.paypal.accountIslinked;
      }
    },
    methods: {
      paypalUnlink() {
        this.$segment.track('CKT PayPal use another account', {
          category: 'ps_checkout'
        });
        this.$store.dispatch('unlink').then(() => {
          this.$store.dispatch('getOnboardingLink');
          this.sendTrack();
        });
      }
    }
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
  .nobootstrap .table tr:last-child td {
    border-bottom: 0 !important;
  }

  .line-separator {
    height: 1px;
    opacity: 0.2;
    background: #6b868f;
    border-bottom: 2px solid #6b868f;
  }
  #app .modal {
    background: rgba(0, 0, 0, 0.4);
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
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.2s;
  }
  .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
    opacity: 0;
  }
  .fs-14 {
    font-size: 14px;
  }
</style>
