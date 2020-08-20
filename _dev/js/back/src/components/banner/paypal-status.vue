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
        <div
          v-if="
            cardPaymentIsActive === 'IN_REVIEW' ||
              cardPaymentIsActive === 'LIMITED' ||
              cardPaymentIsActive === 'NEED_MORE_DATA'
          "
        >
          <p class="mb-0">
            {{ $t('banner.paypalStatus.congrats') }}
          </p>
          <h3 class="mb-4">
            {{ $t('banner.paypalStatus.oneMoreThing') }}
          </h3>
        </div>
        <div v-else>
          <h3 class="mb-0">
            {{ $t('banner.paypalStatus.allSet') }}
          </h3>
          <p class="mb-4">
            {{ $t('banner.paypalStatus.confirmation') }}
          </p>
        </div>

        <p class="mb-3 text-muted">
          <span class="material-icons">
            check_circle
          </span>
          {{ $t('banner.paypalStatus.psAccountConnected') }}
        </p>
        <p class="mb-3 text-muted">
          <span class="material-icons">
            check_circle
          </span>
          {{ $t('banner.paypalStatus.paypalAccountConnected') }}
        </p>
        <p
          class="mb-4"
          v-if="
            cardPaymentIsActive === 'IN_REVIEW' ||
              cardPaymentIsActive === 'LIMITED'
          "
        >
          <span class="material-icons circle">
            stop_circle
          </span>
          {{ $t('banner.paypalStatus.legalDocumentsSent') }}
          {{ $t('banner.paypalStatus.upTo') }}
        </p>
        <p class="mb-4" v-else-if="cardPaymentIsActive === 'NEED_MORE_DATA'">
          <span class="material-icons circle">
            stop_circle
          </span>
          {{ $t('banner.paypalStatus.legalDocumentsSent') }}
          {{ $t('banner.paypalStatus.onlyCC') }}
        </p>
        <p class="mb-4 text-muted" v-else>
          <span class="material-icons">
            check_circle
          </span>
          {{ $t('banner.paypalStatus.legalDocumentsSent') }}
          {{ $t('banner.paypalStatus.upTo') }}
        </p>
        <a
          href="https://www.paypal.com/policy/hub/kyc"
          class="btn btn-primary"
          target="_blank"
          v-if="
            cardPaymentIsActive === 'IN_REVIEW' ||
              cardPaymentIsActive === 'LIMITED' ||
              cardPaymentIsActive === 'NEED_MORE_DATA'
          "
        >
          {{ $t('banner.paypalStatus.buttonLegal') }}
        </a>
        <a
          href="#"
          class="btn btn-primary"
          @click.prevent="updatePaypalStatusSettings()"
          v-else
        >
          {{ $t('banner.paypalStatus.buttonSuccess') }}
        </a>
        <p
          class="mt-2 text-muted"
          v-if="
            cardPaymentIsActive === 'IN_REVIEW' ||
              cardPaymentIsActive === 'LIMITED' ||
              cardPaymentIsActive === 'NEED_MORE_DATA'
          "
        >
          {{ $t('banner.paypalStatus.waitingFinalApprove') }}
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
        return 'NEED_MORE_DATA';//this.$store.state.paypal.cardIsActive;
      }
    }
  };
</script>

<style scoped>
  .material-icons {
    color: #4bbb6c;
  }
  .material-icons.circle {
    color: #b5c7cd;
  }
</style>
