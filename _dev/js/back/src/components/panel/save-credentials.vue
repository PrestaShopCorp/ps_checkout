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
      {{ $t('panel.save-credentials.title') }}
    </template>

    <b-card-body>
      <b-col sm="10" md="10" lg="10" class="m-auto">
        <b-card-title>
          {{ $t('panel.save-credentials.globalSettings') }}
        </b-card-title>

        <div class="m-auto pb-3">
          <div class="d-flex">
            <span>{{ $t('panel.save-credentials.enableSaveCards') }}</span>
            <PSSwitch
              :id="123"
              :position="1"
              text-position="none"
              v-model="cardSavePaypalIsEnabled"
              @input="cardSavePaypalIsEnabled"
            >
              <template v-if="cardSavePaypalIsEnabled">
                {{ $t('panel.active-payment.enabled') }}
              </template>
              <template v-else>
                {{ $t('panel.active-payment.disabled') }}
              </template>
            </PSSwitch>
          </div>
        </div>

        <b-alert variant="info" show>
          <h4 class="alert-heading">
            {{ $t('panel.save-credentials.tipsTitle') }}
          </h4>
          <p>
            {{ $t('panel.save-credentials.tipsContent') }}
          </p>
        </b-alert>
      </b-col>
    </b-card-body>
  </b-card>
</template>

<script>
  import PSSwitch from '@/components/form/switch';

  export default {
    components: {
      PSSwitch
    },
    data() {
      return {
        cardSavePaypalIsEnabled: this.$store.state.configuration.cardSavePaypalIsEnabled
      };
    },
    watch: {
      cardSavePaypalIsEnabled(val) {
        this.$store.dispatch({
          type: 'toggleSaveCreditCards',
          cardSavePaypalIsEnabled: val
        });
      }
    },
    methods: {
      toggleSaveCreditCards(value) {
        this.$store.dispatch({
          type: 'toggleSaveCreditCards',
          paymentOption: {
            isEnabled: value
          }
        });
      },
    },
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
