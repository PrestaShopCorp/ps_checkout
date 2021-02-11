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
    <b-alert variant="warning" show>
      <h2>{{ $t('banner.paypalIncompatibleCurrency.title') }}</h2>

      <p class="mb-3">
        {{ $t('banner.paypalIncompatibleCurrency.content') }}
      </p>

      <p>
        <span
          v-for="(incompatibleCurrencyCode, index) in incompatibleCurrencyCodes"
          :key="incompatibleCurrencyCode"
        >
          <b>
            <i>{{ incompatibleCurrencyCode }}</i>
          </b>

          <span v-if="index != incompatibleCurrencyCodes.length - 1">
            <b><i>,&nbsp;</i></b>
          </span>
        </span>
      </p>

      <p class="mt-4">
        <a
          :href="currenciesLink"
          class="btn btn-primary"
          target="_blank"
          @click="clickChangeCodes"
        >
          {{ $t('banner.paypalIncompatibleCurrency.changeCodes') }}
        </a>

        <a
          :href="paymentPreferencesLink"
          class="btn btn-primary ml-4"
          target="_blank"
          @click="clickChangeActivation"
        >
          {{ $t('banner.paypalIncompatibleCurrency.changeActivation') }}
        </a>

        <b-button
          variant="link"
          href="https://developer.paypal.com/docs/api/reference/currency-codes/#"
          target="_blank"
          class="ml-4"
        >
          {{ $t('banner.paypalIncompatibleCurrency.more') }}

          <i class="material-icons">trending_flat</i>
        </b-button>
      </p>
    </b-alert>
  </div>
</template>

<script>
  export default {
    name: 'PaypalIncompatibleCurrency',
    computed: {
      incompatibleCurrencyCodes() {
        return this.$store.getters.incompatibleCurrencyCodes;
      },
      currenciesLink() {
        return this.$store.getters.currenciesLink;
      },
      paymentPreferencesLink() {
        return this.$store.getters.paymentPreferencesLink;
      }
    },
    methods: {
      clickChangeCodes() {
        this.$segment.track(
          'Clicked on "Change currencies ISO Codes"',
          { category: 'ps_checkout' }
        );
      },
      clickChangeActivation() {
        this.$segment.track(
          'Clicked on "Change currencies activation for this payment module"',
          { category: 'ps_checkout' }
        );
      }
    }
  };
</script>
