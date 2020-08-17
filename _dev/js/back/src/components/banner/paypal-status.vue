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
  <div class="card" v-if="this.displayLiveStep">
    <div class="card-body d-flex">
      <div class="d-flex">
        <img src="@/assets/images/cb.png" alt="cb" />
      </div>
      <div class="flex-grow-1">
        <h3 class="mb-0">
          {{ $t('banner.paypal-status.all-set') }}
        </h3>
        <p class="mb-4">
          {{ $t('banner.paypal-status.confirmation') }}
        </p>
        <p class="mb-3 text-muted">
          <img src="@/assets/images/baseline-check_circle.png" style="width:25px;" alt="cb" />
          {{ $t('banner.paypal-status.ps-account-connected') }}
        </p>
        <p class="mb-3 text-muted">
          <img src="@/assets/images/baseline-check_circle.png" style="width:25px;" alt="cb" />
          {{ $t('banner.paypal-status.paypal-account-connected') }}
        </p>
        <p class="mb-4 text-muted">
          <img src="@/assets/images/baseline-check_circle.png" style="width:25px;" alt="cb" />
          {{ $t('banner.paypal-status.legal-documents-sent') }}
        </p>

        <p>
          <a
            href="#"
            class="btn btn-primary"
            @click.prevent="updatePaypalStatusSettings()"
          >
            {{ $t('banner.paypal-status.button-success') }}
          </a>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'PaypalStatusBanner',
    data() {
      return {
        displayLiveStep: true
      };
    },
    methods: {
      updatePaypalStatusSettings() {
        this.$store.dispatch('updatePaypalStatusSettings').then(() => {
          this.displayLiveStep = false;
        });
      }
    },
    computed: {
      cardPaymentIsActive() {
        return this.$store.state.paypal.cardIsActive;
      }
    }
  };
</script>
