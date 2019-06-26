<template>
  <div class="card">
    <h3 class="card-header">
      <i class="material-icons">toggle_on</i> Payment methods activation
    </h3>
    <div class="card-block">
      <form class="form container form-horizontal py-4">
        <div class="card-text">
          <div class="form-group row">
            <label class="form-control-label">
              Payment action
              <span class="help-box" data-toggle="popover" data-content="Name of the second domain of your shop, (e.g. myshop-media-server-1.com). If you do not have another domain, leave this field blank." data-original-title="" title=""/>
            </label>
            <p class="sr-only">Name of the second domain of your shop, (e.g. myshop-media-server-1.com). If you do not have another domain, leave this field blank.</p>
            <div class="col-sm">
              <div class="btn-group" role="group" aria-label="First group">
                <button type="button" @click="setCaptureMode('CAPTURE')" :class="{active : captureMode === 'CAPTURE'}" class="btn btn-primary-reverse btn-outline-primary">CAPTURE</button>
                <button type="button" @click="setCaptureMode('AUTHORIZE')" :class="{active : captureMode === 'AUTHORIZE'}" class="btn btn-primary-reverse btn-outline-primary">AUTHORIZE</button>
              </div>
            </div>
          </div>
          <PSAlert :alert-type="ALERT_TYPE_INFO">
            <p>We recommend Authorize process only for lean manufacturers and craft products sellers.</p>
          </PSAlert>
          <div class="form-group row">
            <label class="form-control-label">
              Environment
            </label>
            <!-- <p class="sr-only">Name of the second domain of your shop, (e.g. myshop-media-server-1.com). If you do not have another domain, leave this field blank.</p> -->
            <div class="col-sm">
              <input v-if="paymentMode === 'LIVE'" class="form-control" type="text" readonly value="Production mode">
              <input v-else class="form-control" type="text" readonly value="Sandbox mode">
              <small class="form-text mb-3">
                <template v-if="paymentMode === 'LIVE'">
                  Production mode enables you to collect your payments.
                </template>
                <template v-else>
                  Test mode doesn’t allow you to collect payments.
                </template>
              </small>
              <a v-if="paymentMode === 'LIVE'" href="#" @click.prevent="updatePaymentMode('LIVE')">Use test mode</a>
              <a v-else href="#" @click.prevent="updatePaymentMode('SANDBOX')">Use production mode</a>
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
    data() {
      return {
        test: 'CAPTURE',
      };
    },
    components: {
      PSAlert,
    },
    methods: {
      setCaptureMode(captureMode) {
        this.$store.dispatch('updateCaptureMode', captureMode);
      },
      updatePaymentMode() {
        let mode = '';
        if (this.paymentMode === 'LIVE') {
          mode = 'SANDBOX';
        } else {
          mode = 'LIVE';
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

<style scoped>

</style>
