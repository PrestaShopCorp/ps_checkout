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
    <h3 class="card-header">
      <i class="material-icons">payments</i>
      {{ $t('panel.payment-method-activation.title') }}
    </h3>
    <div class="card-body ml-5" v-if="cardInlineEnabled">
      <PSSwitch
        id="cardActivation"
        text-position="left"
        v-model="cardInlinePaypalIsEnabled"
      >
        <template>
          {{ $t('panel.payment-method-activation.label') }}
          <span class="checkout-popover">
            <button
              id="popover-activation-card"
              type="button"
              class="btn mr-4"
            >
              <i class="material-icons-outlined info">info</i>
            </button>
            <b-popover
              target="popover-activation-card"
              triggers="click hover"
              placement="bottom"
            >
              <template class="popover-body">
                <i class="material-icons-outlined wb_incandescent">wb_incandescent</i>
                <b>{{ $t('panel.payment-method-activation.popover-difference-question') }}</b>
                <br />
                <br />
                {{ $t('panel.payment-method-activation.popover-difference-answer-begin') }} <a href="https://www.prestashop.com/en/prestashop-checkout" target="_blank">www.prestashop.com/en/prestashop-checkout</a>
                {{ $t('panel.payment-method-activation.popover-difference-answer-end') }} <a href="#" @click.prevent="goToAuthenticate()">Authentication tab</a>
                <br />
                <br />
                <b>{{ $t('panel.payment-method-activation.popover-when-question') }}</b>
                <br />
                <br />
                {{ $t('panel.payment-method-activation.popover-when-answer') }}
              </template>
            </b-popover>
          </span>
        </template>
      </PSSwitch>
    </div>
    <div class="card-body mx-5 my-3" v-else>
      <b>{{ $t('panel.payment-method-activation.disable') }}</b>
    </div>
  </div>
</template>

<script>
  import PSSwitch from '@/components/form/switch';
  export default {
    name: 'CbInlineActivate',
    components: { PSSwitch },
    methods: {
      goToAuthenticate() {
        this.$router
          .push('/authentication')
          .catch(exception => console.log(exception));
      }
    },
    computed: {
      cardInlinePaypalIsEnabled: {
        get() {
          return this.$store.state.configuration.cardInlinePaypalIsEnabled;
        },
        set(payload) {
          this.$store.dispatch('toggleCardInlinePayPalField', payload);
        }
      },
      cardInlineEnabled() {
        return (
          !this.$store.state.configuration.cardIsEnabled ||
          this.$store.state.paypal.cardIsActive === 'DENIED' ||
          this.$store.state.paypal.cardIsActive === 'SUSPENDED' ||
          this.$store.state.paypal.cardIsActive === 'NEED_MORE_DATA' ||
          this.$store.state.paypal.cardIsActive === 'REVOKE'
        );
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
    top: 10px !important;
    background: #e5e1f9;
    color: black;
    padding: 30px 24px 40px 40px;
    font-size: 12px;
    line-height: 18px;
    max-width: 500px;
  }
  .material-icons-outlined.wb_incandescent {
    transform: rotate(180deg);
    margin-right: 10px;
    font-size: 18px;
  }
</style>
<style scoped>
  #app .card-body label {
    font-weight: bold;
  }

  #app .card-body #popover-activation-card {
    background-color: transparent;
  }

  #app .material-icons-outlined.info {
    font-size: 20px;
    margin-left: 2px;
  }
</style>
