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
      {{ $t('panel.active-payment.activePaymentMethods') }}
    </template>

    <b-card-body>
      <b-col sm="10" md="10" lg="10" class="m-auto">
        <b-card-title>
          {{ $t('panel.active-payment.changeOrder') }}
        </b-card-title>

        <div class="m-auto payment-method-container pb-3">
          <draggable
            tag="div"
            v-model="list"
            v-bind="dragOptions"
            handle=".handle"
            @start="drag = true"
            @end="drag = false"
          >
            <transition-group
              type="transition"
              :name="!drag ? 'flip-list' : null"
            >
              <div
                v-for="(element, index) in list"
                :key="element.position"
                class="d-flex"
              >
                <div
                  class="payment-method text-muted d-flex flex-grow-1"
                  :class="{
                    disable: !element.isToggleable
                  }"
                >
                  <div class="position">
                    {{ index + 1 }}
                  </div>
                  <div class="icon">
                    <i class="material-icons handle ml-2 mr-2">
                      drag_indicator
                    </i>
                  </div>
                  <div class="ghost-replace-card">
                    <i class="material-icons text-center">save_alt</i>
                  </div>
                  <div class="flex-grow-1 content">
                    <div class="d-flex payment-method-content">
                      <div class="flex-grow-1">
                        <label class="mb-0 label">
                          <img
                            class="mr-3 logo"
                            :src="getLogo(element)"
                            alt=""
                          />

                          <span v-if="element.name === 'card'">
                            {{ $t('panel.active-payment.creditCard') }}
                          </span>

                          <span v-else>
                            {{ element.label }}
                          </span>
                        </label>
                      </div>

                      <div class="d-none d-lg-flex mr-5">
                        {{ $t('panel.active-payment.availableIn') }}

                        <span
                          v-if="element.countries.length === 0"
                          class="ml-1 country"
                        >
                          {{ $t('panel.active-payment.allCountries') }}
                        </span>

                        <span v-else class="ml-1 country">
                          {{ element.countries.join(', ') }}
                        </span>
                      </div>

                      <div class="d-flex status">
                        <CardStatus
                          class="mr-2"
                          v-if="cardIsAvailable === false"
                        />

                        <PSSwitch
                          v-if="element.isToggleable"
                          :id="element.name"
                          :position="index + 1"
                          text-position="left"
                          v-model="element.isEnabled"
                          @input="sendPaymentOptions"
                        >
                          <template v-if="element.isEnabled">
                            {{ $t('panel.active-payment.enabled') }}
                          </template>
                          <template v-else>
                            {{ $t('panel.active-payment.disabled') }}
                          </template>
                        </PSSwitch>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </transition-group>
          </draggable>
        </div>

        <b-alert variant="info" show>
          <h4 class="alert-heading">
            {{ $t('panel.active-payment.tipsTitle') }}
          </h4>
          <p>
            {{ $t('panel.active-payment.tipsContent') }}
          </p>
        </b-alert>
      </b-col>
    </b-card-body>
  </b-card>
</template>

<script>
  import draggable from 'vuedraggable';
  import CardStatus from '@/components/block/card-status';
  import PSSwitch from '@/components/form/switch';

  export default {
    components: {
      CardStatus,
      PSSwitch,
      draggable
    },
    data() {
      return {
        list: this.$store.state.configuration.paymentMethods,
        drag: false
      };
    },
    watch: {
      list(val) {
        this.$store.dispatch({
          type: 'updatePaymentMethods',
          paymentMethods: val
        });
        let paymentMethodList = [];
        this.list.forEach(element => {
          paymentMethodList.push(element.name);
        });
        this.$segment.track('CKT Change Payment Methods Order', {
          category: 'ps_checkout',
          payment_methods: paymentMethodList
        });
      }
    },
    methods: {
      sendPaymentOptions(value, id, position) {
        this.$store.dispatch({
          type: 'togglePaymentOptionAvailability',
          paymentOption: {
            name: id,
            position: position,
            isEnabled: value
          }
        });
        this.$segment.track(
          value ? 'CKT Enable payment method' : 'CKT Disable payment method',
          {
            category: 'ps_checkout',
            payment_method: id
          }
        );
      },
      getLogo(val) {
        if (!val.name) {
          return '';
        } else {
          return require('@/assets/images/funding-sources/' +
            val.name +
            '.svg');
        }
      }
    },
    computed: {
      dragOptions() {
        return {
          animation: 200,
          group: 'description',
          disabled: false,
          ghostClass: 'ghost',
          dragClass: 'move'
        };
      },
      cardIsAvailable() {
        return (
          this.$store.state.paypal.cardIsActive === 'SUBSCRIBED' ||
          this.$store.state.paypal.cardIsActive === 'LIMITED'
        );
      }
    }
  };
</script>

<style scoped>
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
  .payment-method-container img {
    width: 25px;
  }
  .payment-method-container .flex-grow-1.content i {
    color: #25b9d7;
  }
  .payment-method {
    position: relative;
    display: block;
    margin-top: 10px;
    margin-bottom: 10px;
    background-color: #fff;
    border: 1px solid #dddddd;
    border-radius: 3px;
  }
  .payment-method.disable {
    background-color: #fafbfc;
  }
  .payment-method.disable .material-icons {
    color: #759299 !important;
  }
  .position {
    position: absolute;
    top: 20px;
    left: 40px;
  }
  .payment-method-content {
    padding: 20px;
  }
  .flex-grow-1 {
    flex-grow: 1;
  }
  .separator {
    border-top: 1px solid #dddddd;
  }
  .label {
    font-weight: 550;
    color: #000000;
  }
  .logo {
    max-height: 30px;
    min-width: 60px;
    max-width: 90px;
  }
  .country {
    font-weight: 600;
    text-align: right;
  }
  @media screen and (min-width: 992px) {
    .status {
      min-width: 10em;
    }
  }
</style>
