<template>
  <div class="card">
    <h3 class="card-header">
      <i class="material-icons">toggle_on</i> {{ $t('panel.active-payment.activePaymentMethods') }}
    </h3>
    <div class="card-block">
      <div class="container-fluid py-3 col-10">
        <label class="title mr-4">{{ $t('panel.active-payment.paymentMethods') }}</label> <label class="text-muted">{{ $t('panel.active-payment.changeOrder') }}</label>
      </div>
      <div class="m-auto payment-method-container pb-3">
        <draggable
          tag="div"
          v-model="list"
          v-bind="dragOptions"
          handle=".handle"
          @start="drag = true"
          @end="drag = false"
        >
          <transition-group type="transition" :name="!drag ? 'flip-list' : null">
            <div
              v-for="(element, index) in list"
              :key="index"
              class="d-flex"
            >
              <div class="payment-method text-muted d-flex flex-grow-1">
                <div class="position">
                  {{ index + 1 }}
                </div>
                <div class="icon">
                  <i class="material-icons handle ml-2 mr-2">drag_indicator</i>
                </div>
                <div v-if="element.name === 'card'" class="ghost-replace-card">
                  <i class="material-icons text-center">save_alt</i>
                </div>
                <div v-if="element.name === 'paypal'" class="ghost-replace-paypal">
                  <i class="material-icons">save_alt</i>
                </div>
                <div class="flex-grow-1 content">
                  <div class="d-flex payment-method-content">
                    <div class="flex-grow-1">
                      <label v-if="element.name === 'card'" class="mb-0"><i class="material-icons">credit_card</i> {{ $t('panel.active-payment.creditCard') }}</label>
                      <label v-else class="mb-0"><i class="material-icons">account_balance</i> {{ $t('panel.active-payment.paypal') }}</label>
                    </div>
                    <div class="status">
                      <template v-if="element.name === 'card'"><CardStatus /></template>
                      <template v-else><PaypalStatus /></template>
                    </div>
                  </div>
                  <div v-if="element.name === 'paypal'" class="d-flex payment-method-content separator">
                    <div class="flex-grow-1">
                      <label class="mb-0"><i class="material-icons">public</i> {{ $t('panel.active-payment.localPaymentMethods') }}</label>
                    </div>
                    <div class="status">
                      <LpmStatus />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </transition-group>
        </draggable>
      </div>
    </div>
  </div>
</template>

<script>
  import draggable from 'vuedraggable';
  import CardStatus from '@/components/block/card-status';
  import PaypalStatus from '@/components/block/paypal-status';
  import LpmStatus from '@/components/block/lpm-status';

  export default {
    components: {
      CardStatus,
      PaypalStatus,
      LpmStatus,
      draggable,
    },
    data() {
      return {
        list: this.$store.state.configuration.paymentMethods,
        drag: false,
      };
    },
    watch: {
      list(val) {
        this.$store.dispatch({
          type: 'updatePaymentMethods',
          paymentMethods: val,
        });
      },
    },
    computed: {
      dragOptions() {
        return {
          animation: 200,
          group: 'description',
          disabled: false,
          ghostClass: 'ghost',
          dragClass: 'move',
        };
      },
    },
  };
</script>

<style scoped>
  .title {
    font-weight: 600 !important;
  }
  .handle {
    cursor: grab;
    margin-top: 20px;
  }
  .handle:hover {
    color: #25b9d7;
  }
  .sortable-chosen .handle {
    cursor: grabbing;
  }
  .move {
    cursor: grabbing;
  }
  .move .position {
    display: none;
  }
  .move .payment-method {
    cursor: grabbing;
    margin-left: 40px;
  }
  .ghost .payment-method {
    border: 2px dashed #25b9d7;
    background-color: #fcfcfc;
  }
  .ghost .icon {
    display: none;
  }
  .ghost .content {
    display: none;
  }
  .ghost .ghost-replace-card {
    display: block !important;
  }
  .ghost .ghost-replace-paypal {
    display: block !important;
  }
  .ghost-replace-card {
    display: none;
    padding: 20px;
    height: 64.27px;
    text-align: center;
    width: 100%;
  }
  .ghost-replace-paypal {
    display: none;
    padding: 20px;
    height: 129.53px;
    width: 100%;
    text-align: center;
    line-height: 6;
  }
  .move .number {
    display: none;
  }
  .payment-method-container {
    max-width: 500px;
  }
  .payment-method {
    position: relative;
    display: block;
    margin-top: 10px;
    margin-bottom: 10px;
    background-color: #fff;
    border: 1px solid #dddddd;
    border-radius: 3px;
    max-width: 475px;
  }
  .position {
    position: absolute;
    top: 20px;
    left: -40px;
  }
  .payment-method-content {
    padding: 20px;
  }
  .flex-grow-1 {
    flex-grow: 1;
  }
  .separator {
    border-top: 1px solid #DDDDDD;
  }
</style>
