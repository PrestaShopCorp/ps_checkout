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
        !isLiveStepConfirmed && firebaseStatusAccount && paypalStatusAccount
      "
    >
      <PaypalStatusBanner />
    </b-container>

    <b-container
      class="mb-4"
      v-if="
        firebaseStatusAccount && paypalStatusAccount && incompatibleCountryCodes
      "
    >
      <PaypalIncompatibleCountry />
    </b-container>

    <b-container
      class="mb-4"
      v-if="
        firebaseStatusAccount &&
          paypalStatusAccount &&
          incompatibleCurrencyCodes
      "
    >
      <PaypalIncompatibleCurrency />
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

    <b-container class="mt-4" v-else>
      <Reassurance />
    </b-container>
  </div>
</template>

<script>
  import AccountList from '@/components/panel/account-list';
  import PaymentAcceptance from '@/components/panel/payment-acceptance';
  import Reassurance from '@/components/block/reassurance';
  import PaypalStatusBanner from '@/components/banner/paypal-status';
  import PaypalIncompatibleCountry from '@/components/banner/paypal-incompatible-country';
  import PaypalIncompatibleCurrency from '@/components/banner/paypal-incompatible-currency';

  export default {
    name: 'Accounts',
    components: {
      AccountList,
      PaymentAcceptance,
      Reassurance,
      PaypalStatusBanner,
      PaypalIncompatibleCountry,
      PaypalIncompatibleCurrency
    },
    computed: {
      firebaseStatusAccount() {
        return this.$store.state.firebase.onboardingCompleted;
      },
      paypalStatusAccount() {
        return this.$store.state.paypal.onboardingCompleted;
      },
      isLiveStepConfirmed() {
        return this.$store.state.context.liveStepConfirmed;
      },
      incompatibleCountryCodes() {
        return this.$store.state.context.incompatibleCountryCodes;
      },
      incompatibleCurrencyCodes() {
        return this.$store.state.context.incompatibleCurrencyCodes;
      }
    }
  };
</script>
