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
  <b-card no-body>
    <template v-slot:header>
      <i class="material-icons">toggle_on</i>
      {{ $t('panel.payment-mode.title') }}
    </template>
    <b-card-body>
      <b-form>
        <b-form-group
          label-cols="4"
          label-align="right"
          :label="$t('panel.payment-mode.paymentAction')"
          label-for="intent-mode"
        >
          <b-form-radio-group
            id="intent-mode"
            v-model="captureMode"
            :options="intentOptions"
            buttons
            button-variant="outline-primary"
            name="radio-btn-outline"
          />
          <b-popover
            target="intent-mode"
            triggers="hover focus"
            placement="rightbottom"
          >
            <template class="popover-body">
              <i class="material-icons-outlined wb_incandescent">wb_incandescent</i>
              <b>{{ $t('panel.payment-mode.tipdirectsale-question') }}</b>
              <br />
              <br />
              {{ $t('panel.payment-mode.tipdirectsale-answer') }}
              <br />
              <br />
              <b>{{ $t('panel.payment-mode.tipcapture-question') }}</b>
              <br />
              <br />
              {{ $t('panel.payment-mode.tipcapture-answer') }}
            </template>
          </b-popover>
        </b-form-group>

        <b-alert class="d-inline-block w-100" variant="info" show>
          <p>{{ $t('panel.payment-mode.infoAlertText') }}.</p>
        </b-alert>

        <b-form-group
          v-if="paymentMode === 'LIVE'"
          label-cols="4"
          label-align="right"
          :description="$t('panel.payment-mode.tipProductionMode')"
          :label="$t('panel.payment-mode.environment')"
          label-for="production-input"
        >
          <b-form-input
            id="production-input"
            :value="$t('panel.payment-mode.productionMode')"
            disabled
          />
        </b-form-group>

        <b-form-group
          v-else
          label-cols="4"
          label-align="right"
          :description="$t('panel.payment-mode.tipSandboxMode')"
          :label="$t('panel.payment-mode.environment')"
          label-for="sandbox-input"
        >
          <b-form-input
            id="sandbox-input"
            :value="$t('panel.payment-mode.sandboxMode')"
            disabled
          />
        </b-form-group>

        <b-form-group label-cols="4" label-for="update-mode">
          <b-button
            id="update-mode"
            @click="updatePaymentMode()"
            variant="link"
            class="px-0"
          >
            <template v-if="paymentMode === 'LIVE'">
              {{ $t('panel.payment-mode.useSandboxMode') }}
            </template>
            <template v-else>
              {{ $t('panel.payment-mode.useProductionMode') }}
            </template>
          </b-button>
        </b-form-group>
      </b-form>
    </b-card-body>
  </b-card>
</template>

<script>
  export default {
    data() {
      return {
        intentOptions: [
          { text: this.$t('panel.payment-mode.capture'), value: 'CAPTURE' },
          { text: this.$t('panel.payment-mode.authorize'), value: 'AUTHORIZE' }
        ]
      };
    },
    methods: {
      updatePaymentMode() {
        let mode = 'LIVE';
        if (this.paymentMode === 'LIVE') {
          mode = 'SANDBOX';
        }

        this.$store.dispatch('updatePaymentMode', mode).then(() => {
          this.$store.dispatch('getOnboardingLink');
        });
      }
    },
    computed: {
      captureMode: {
        get() {
          return this.$store.state.configuration.captureMode;
        },
        set(value) {
          if (this.captureMode === value) {
            return;
          }
          this.$store.dispatch('updateCaptureMode', value);
        }
      },
      paymentMode() {
        return this.$store.state.configuration.paymentMode;
      }
    }
  };
</script>

<style lang="scss">
  .checkout-popover {
    vertical-align: bottom !important;
    button {
      vertical-align: middle;
      text-transform: lowercase !important;
      background-color: #fff !important;
      border: 0px !important;
      color: #7b9399 !important;
      padding: 0 !important;
      font-weight: bold !important;
      line-height: 0px !important;
      span {
        font-size: 16px;
        vertical-align: middle;
      }
      &:focus {
        outline: inherit !important;
      }
      &:hover {
        opacity: 0.6;
        color: #25b9d7 !important;
      }
    }
  }
  .b-popover {
    left: 20px !important;
    top: -12px !important;
    background: #e5e1f9;
    color: black;
    padding: 23px 28px;
    font-size: 12px;
    line-height: 18px;
    max-width: 300px;
    b {
      font-size: 14px;
    }
  }
  .material-icons-outlined.wb_incandescent {
    transform: rotate(180deg);
    margin-right: 10px;
    font-size: 18px;
    color: #fd1080;
  }
</style>
