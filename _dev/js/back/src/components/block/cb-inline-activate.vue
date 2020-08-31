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
    <div class="card-body ml-5">
      <PSSwitch
        id="cardActivation"
        text-position="left"
        v-model="cardInlinePaypalIsEnabled"
        :disable="!cardInlineEnabled"
      >
        <template>
          {{ $t('panel.payment-method-activation.label') }}
          <span class="checkout-popover">
            <button
              id="popover-activation-card"
              type="button"
              class="btn"
            >
              <i class="material-icons">info_outline</i>
            </button>
            <b-popover
              target="popover-activation-card"
              triggers="click hover"
              placement="bottom"
            >
              <template>
                {{ $t('panel.payment-method-activation.popover-difference-question') }}
                <br>
                {{ $t('panel.payment-method-activation.popover-difference-answer') }}
                <br>
                {{ $t('panel.payment-method-activation.popover-when-question') }}
                <br>
                {{ $t('panel.payment-method-activation.popover-when-answer') }}
              </template>
            </b-popover>
          </span>
        </template>
      </PSSwitch>
    </div>
  </div>
</template>

<script>
  import PSSwitch from '@/components/form/switch';
  export default {
    name: 'CbInlineActivate',
    components: { PSSwitch },
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

<style scoped>
  #app .card-body label {
    font-weight: bold;
  }

  #app .card-body #popover-activation-card {
    background-color: transparent;
  }

  #app .checkout-popover {
    vertical-align: bottom !important;
  }

  #app .checkout-popover button {
    vertical-align: middle;
    text-transform: lowercase !important;
    background-color: #fff !important;
    border: 0px !important;
    color: #7b9399 !important;
    padding: 0 !important;
    font-weight: bold !important;
    line-height: 0px !important;
  }
  #app .checkout-popover button:focus {
    outline: inherit !important;
  }

  #app .checkout-popover button:hover {
    opacity: 0.6;
    color: #25b9d7 !important;
  }

  #app .checkout-popover button span {
    font-size: 16px;
    vertical-align: middle;
  }

  #app .b-popover {
    top: -10px !important;
    background: rgba(0, 0, 0, 0.8);
    color: #fff;
    padding: 20px;
  }
</style>

<style lang="scss">

</script>
