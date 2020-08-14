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
  <div class="card">
    <div class="card-body d-flex">
      <div class="d-flex">
        <img src="@/assets/images/cb.png" alt="cb" />
      </div>
      <div class="flex-grow-1">
        <h3>
          {{ $t('banner.paypal-status.all-set') }}
        </h3>
        <p class="mb-3">
          {{ $t('banner.paypal-status.confirmation') }}
        </p>
        <p class="mb-3">
          <img src="@/assets/images/baseline-check_circle.png" alt="cb" />
          {{ $t('banner.paypal-status.confirmation') }}
        </p>
        <p>
          <b-button
            target="_blank"
            variant="outline-secondary"
            @click="updatePaypalStatusSettings()"
          >
            {{ $t('banner.paypal-status.button-success') }}
          </b-button>
        </p>
      </div>
    </div>
    <div>
      <b-alert v-if="confirmationAlert" variant="success" show>
        <h2>{{ $t('banner.paypal-status.confirmationTitle') }}</h2>
        <p class="mb-3">
          {{ $t('banner.paypal-status.confirmationLabel') }}
        </p>
      </b-alert>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'PaypalStatusBanner',
    data() {
      return {
        confirmationAlert: false
      };
    },
    methods: {
      updatePaypalStatusSettings() {
        this.$store.dispatch('updatePaypalStatusSettings').then(() => {
          this.confirmationAlert = true;
        });
      }
    },
    computed: {
      cardPaymentIsActive() {
        return this.$store.state.paypal.cardIsActive;
      }
    },
    watch: {
      confirmationAlert(value) {
        if (value === false) {
          return;
        }
        setTimeout(() => {
          this.confirmationAlert = false;
        }, 6000);
      }
    }
  };
</script>
