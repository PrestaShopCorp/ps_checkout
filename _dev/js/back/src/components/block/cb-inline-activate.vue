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
        :class="{ disable: false }"
      >
        <template>
          {{ $t('panel.payment-method-activation.label') }}
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
      }
    }
  };
</script>

<style scoped>
  #app .card-body label {
    font-weight: bold;
  }
</style>
