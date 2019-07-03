<template>
  <div class="card">
    <h3 class="card-header">
      <i class="material-icons">toggle_on</i> {{ $t('panel.payment-mode.title') }}
    </h3>
    <div class="card-block">
      <form class="form container form-horizontal py-4">
        <div class="card-text">
          <div class="form-group row">
            <label class="form-control-label">
              {{ $t('panel.payment-mode.paymentAction') }}
              <span class="help-box" data-toggle="popover" :data-content="$t('panel.payment-mode.helpBoxPaymentMode')" data-original-title="" title=""/>
            </label>
            <div class="col-sm">
              <div class="btn-group" role="group" aria-label="First group">
                <button type="button" @click="setCaptureMode('CAPTURE')" :class="{active : captureMode === 'CAPTURE'}" class="btn btn-primary-reverse btn-outline-primary">{{ $t('panel.payment-mode.capture') }}</button>
                <button type="button" @click="setCaptureMode('AUTHORIZE')" :class="{active : captureMode === 'AUTHORIZE'}" class="btn btn-primary-reverse btn-outline-primary">{{ $t('panel.payment-mode.authorize') }}</button>
              </div>
            </div>
          </div>
          <PSAlert :alert-type="ALERT_TYPE_INFO">
            <p>{{ $t('panel.payment-mode.infoAlertText') }}.</p>
          </PSAlert>
          <div class="form-group row">
            <label class="form-control-label">
              {{ $t('panel.payment-mode.environment') }}
            </label>
            <div class="col-sm">
              <input v-if="paymentMode === 'LIVE'" class="form-control" type="text" readonly :value="$t('panel.payment-mode.productionMode')">
              <input v-else class="form-control" type="text" readonly :value="$t('panel.payment-mode.sandboxMode')">
              <small class="form-text mb-3">
                <template v-if="paymentMode === 'LIVE'">
                  {{ $t('panel.payment-mode.tipProductionMode') }}
                </template>
                <template v-else>
                  {{ $t('panel.payment-mode.tipSandboxMode') }}.
                </template>
              </small>
              <a href="#" @click.prevent="updatePaymentMode()">
                <template v-if="paymentMode === 'LIVE'">
                  {{ $t('panel.payment-mode.useSandboxMode') }}
                </template>
                <template v-else>
                  {{ $t('panel.payment-mode.useProductionMode') }}
                </template>
              </a>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
  import PSAlert from '@/components/form/alert';
  import {ALERT_TYPE_INFO} from '@/lib/alert';

  export default {
    components: {
      PSAlert,
    },
    methods: {
      setCaptureMode(captureMode) {
        if (this.captureMode === captureMode) {
          return;
        }
        this.$store.dispatch('updateCaptureMode', captureMode);
      },
      updatePaymentMode() {
        let mode = 'LIVE';
        if (this.paymentMode === 'LIVE') {
          mode = 'SANDBOX';
        }

        this.$store.dispatch('updatePaymentMode', mode);
      },
    },
    computed: {
      ALERT_TYPE_INFO: () => ALERT_TYPE_INFO,
      captureMode() {
        return this.$store.state.configuration.module.captureMode;
      },
      paymentMode() {
        return this.$store.state.configuration.module.paymentMode;
      },
    },
  };
</script>
