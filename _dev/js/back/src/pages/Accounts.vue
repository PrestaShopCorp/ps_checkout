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
  <div>
    <b-container
      class="mb-4"
      v-if="
        !isLiveStepConfirmed &&
          // PS Accounts stand by
          /* (checkoutAccountStatus || prestashopAccountStatus) && */
          checkoutAccountStatus &&
          paypalAccountStatus
      "
    >
      <PaypalStatusBanner />
    </b-container>

    <b-container
      class="mb-4"
      v-if="
        checkoutAccountStatus &&
          // PS Accounts stand by
          /* (checkoutAccountStatus || prestashopAccountStatus) && */
          paypalAccountStatus &&
          incompatibleCountryCodes.length > 0
      "
    >
      <PaypalIncompatibleCountry />
    </b-container>

    <b-container
      class="mb-4"
      v-if="
        checkoutAccountStatus &&
          // PS Accounts stand by
          /* (checkoutAccountStatus || prestashopAccountStatus) && */
          paypalAccountStatus &&
          incompatibleCurrencyCodes.length > 0
      "
    >
      <PaypalIncompatibleCurrency />
    </b-container>

    <b-container
      class="text-center"
      v-if="
        checkoutAccountStatus &&
          // PS Accounts stand by
          /* (!checkoutAccountStatus || !prestashopAccountStatus) && */
          !paypalAccountStatus
      "
    >
      <h2 class="text-muted font-weight-light">
        {{ $t('panel.accounts.activateAllPayment') }}
      </h2>
    </b-container>

    <!-- PS Accounts stand by -->
    <!-- <b-container v-if="!checkoutAccountStatus">
      <PsAccounts>
        <template v-slot:body>
          <PaypalAccount :sendTrack="sendTrack" />
        </template>
      </PsAccounts>
    </b-container> -->

    <!-- <b-container v-else> -->
    <b-container>
      <CheckoutAccount class="mb-3" :sendTrack="sendTrack" />

      <PaypalAccount :sendTrack="sendTrack" />
    </b-container>

    <b-container
      class="mt-4"
      v-if="
        checkoutAccountStatus &&
          // PS Accounts stand by
          /* (checkoutAccountStatus || prestashopAccountStatus) && */
          paypalAccountStatus
      "
    >
      <PaymentAcceptance />
    </b-container>

    <b-container class="mt-4" v-else>
      <Reassurance />
    </b-container>
  </div>
</template>

<script>
  import {
    // PS Accounts stand by
    // PsAccounts,
    isOnboardingCompleted
  } from 'prestashop_accounts_vue_components';
  import PaypalAccount from '@/components/panel/paypal-account';
  import CheckoutAccount from '@/components/panel/checkout-account';
  import PaymentAcceptance from '@/components/panel/payment-acceptance';
  import Reassurance from '@/components/block/reassurance';
  import PaypalStatusBanner from '@/components/banner/paypal-status';
  import PaypalIncompatibleCountry from '@/components/banner/paypal-incompatible-country';
  import PaypalIncompatibleCurrency from '@/components/banner/paypal-incompatible-currency';

  export default {
    name: 'Accounts',
    components: {
      // PS Accounts stand by
      // PsAccounts,
      PaypalAccount,
      CheckoutAccount,
      PaymentAcceptance,
      Reassurance,
      PaypalStatusBanner,
      PaypalIncompatibleCountry,
      PaypalIncompatibleCurrency
    },
    computed: {
      checkoutAccountStatus() {
        return this.$store.state.firebase.onboardingCompleted;
      },
      prestashopAccountStatus() {
        return isOnboardingCompleted();
      },
      paypalAccountStatus() {
        return this.$store.state.paypal.onboardingCompleted;
      },
      isLiveStepConfirmed() {
        return this.$store.state.context.liveStepConfirmed;
      },
      accountIslinked() {
        return this.$store.state.paypal.accountIslinked;
      },
      merchantEmailIsValid() {
        return this.$store.state.paypal.emailIsValid;
      },
      cardPaymentIsActive() {
        return this.$store.state.paypal.cardIsActive;
      },
      incompatibleCountryCodes() {
        return this.$store.state.context.incompatibleCountryCodes;
      },
      incompatibleCurrencyCodes() {
        return this.$store.state.context.incompatibleCurrencyCodes;
      }
    },
    methods: {
      sendTrack() {
        if (
          !this.checkoutAccountStatus &&
          // PS Accounts stand by
          // !this.prestashopAccountStatus &&
          !this.paypalAccountStatus
        ) {
          // Anything connected
          this.$segment.track('View Authentication - Status Logged Out', {
            category: 'ps_checkout'
          });
        } else if (
          this.checkoutAccountStatus &&
          // PS Accounts stand by
          // !this.prestashopAccountStatus &&
          !this.paypalAccountStatus
        ) {
          // Only Checkout account connected
          this.$segment.track(
            'View Authentication - Status Checkout account connected',
            { category: 'ps_checkout' }
          );
          // PS Accounts stand by
          // } else if (
          //   !this.checkoutAccountStatus &&
          //   this.prestashopAccountStatus &&
          //   !this.paypalAccountStatus
          // ) {
          //   // Only PrestaShop account connected
          //   this.$segment.track(
          //     'View Authentication - Status PrestaShop account connected',
          //     { category: 'ps_checkout' }
          //   );
        } else if (
          // PS Accounts stand by
          // (this.checkoutAccountStatus || this.prestashopAccountStatus) &&
          this.checkoutAccountStatus &&
          this.paypalAccountStatus
        ) {
          // Both accounts "connected"
          // PS Accounts stand by
          // let accountType = null;
          //
          // if (this.checkoutAccountStatus) {
          //   accountType = 'PrestaShop Checkout';
          // } else if (this.prestashopAccountStatus) {
          //   accountType = 'PrestaShop Accounts';
          // }

          if (
            this.accountIslinked &&
            this.merchantEmailIsValid &&
            this.cardPaymentIsActive === 'SUBSCRIBED'
          ) {
            // all right
            this.$segment.track(
              'View Authentication screen - Status Both account approved',
              {
                category: 'ps_checkout'
                // PS Accounts stand by
                // category: 'ps_checkout',
                // psAccountType: accountType
              }
            );
          } else {
            // but need approval
            this.$segment.track(
              'View Authentication - Status PP approval pending',
              {
                category: 'ps_checkout'
                // PS Accounts stand by
                // category: 'ps_checkout',
                // psAccountType: accountType
              }
            );
          }
        }
      }
    },
    mounted() {
      this.sendTrack();
    }
  };
</script>
