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
          <div class="d-flex save-card-content">
            <span>{{ $t('panel.save-credentials.enableSaveCards') }}</span>
            <PSSwitch
              id="save-card"
              :position="1"
              text-position="none"
              v-model="cardSavePaypalIsEnabled"
              @input="cardSavePaypalIsEnabled"
            >
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
      toggleSaveCreditCards(val) {
        this.$store.dispatch({
          type: 'toggleSaveCreditCards',
          cardSavePaypalIsEnabled: val
        });
      },
    },
  };
</script>

<style scoped>
.save-card-content {
  justify-content: space-between;
}
</style>
