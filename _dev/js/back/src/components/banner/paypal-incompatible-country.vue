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
      <h2>{{ $t('banner.paypalIncompatibleCountry.title') }}</h2>

      <p class="mb-3">
        {{ $t('banner.paypalIncompatibleCountry.content') }}
      </p>

      <p>
        <span
          v-for="(incompatibleCountryCode, index) in incompatibleCountryCodes"
          :key="incompatibleCountryCode"
        >
          <b>
            <i>{{ incompatibleCountryCode }}</i>
          </b>

          <span v-if="index != incompatibleCountryCodes.length - 1">
            <b><i>,&nbsp;</i></b>
          </span>
        </span>
      </p>

      <p class="mt-4">
        <a
          :href="countriesLink"
          class="btn btn-primary"
          target="_blank"
          @click="clickChangeCodes"
        >
          {{ $t('banner.paypalIncompatibleCountry.changeCodes') }}
        </a>

        <a
          :href="paymentPreferencesLink"
          class="btn btn-primary ml-4"
          target="_blank"
          @click="clickChangeActivation"
        >
          {{ $t('banner.paypalIncompatibleCountry.changeActivation') }}
        </a>

        <b-button
          variant="link"
          href="https://developer.paypal.com/docs/api/reference/country-codes/#"
          target="_blank"
          class="ml-4"
        >
          {{ $t('banner.paypalIncompatibleCountry.more') }}

          <i class="material-icons">trending_flat</i>
        </b-button>
      </p>
    </b-alert>
  </div>
</template>

<script>
  export default {
    name: 'PaypalIncompatibleCountry',
    computed: {
      incompatibleCountryCodes() {
        return this.$store.getters.incompatibleCountryCodes;
      },
      countriesLink() {
        return this.$store.getters.countriesLink;
      },
      paymentPreferencesLink() {
        return this.$store.getters.paymentPreferencesLink;
      }
    },
    methods: {
      clickChangeCodes() {
        this.$segment.track('Clicked on "Change countries ISO Codes"', {
          category: 'ps_checkout'
        });
      },
      clickChangeActivation() {
        this.$segment.track(
          'Clicked on "Change countries activation for this payment module"',
          { category: 'ps_checkout' }
        );
      }
    }
  };
</script>
