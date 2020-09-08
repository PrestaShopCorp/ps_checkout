<!--**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *-->
<template>
  <b-container>
    <b-card no-body>
      <template v-slot:header>
        <i class="material-icons">warning</i> Debug view
      </template>
      <b-card-body>
        <ul>
          <li>PrestaShop version: <b>{{ psVersion }}</b></li>
          <li>PHP version: <b>{{ phpVersion }}</b></li>
          <li>Module version: <b>{{ moduleVersion }}</b></li>
          <li>Shop ID: <b>{{ shopId }}</b></li>
          <li>PSX ID: <b>{{ psxId }}</b></li>
          <li>Merchant ID: <b>{{ merchantId }}</b></li>
          <li>Rounding config: <b>{{ roundingSettingsIsCorrect }}</b></li>
        </ul>
        <PSSwitch
          id="hostedFieldsAvailability"
          v-model="debugLogsEnabled"
        >
          <template v-if="debugLogsEnabled">
            Logs enabled
          </template>
          <template v-else>
            Logs disabled
          </template>
        </PSSwitch>
      </b-card-body>
    </b-card>
  </b-container>
</template>

<script>
  import PSSwitch from '@/components/form/switch';

  export default {
    components: {
      PSSwitch,
    },
    name: 'Debug',
    computed: {
      moduleVersion() {
        return this.$store.state.context.moduleVersion;
      },
      phpVersion() {
        return this.$store.state.context.phpVersion;
      },
      psVersion() {
        return this.$store.state.context.psVersion;
      },
      shopId() {
        return this.$store.state.context.shopId;
      },
      merchantId() {
        return this.$store.state.context.merchantId;
      },
      psxId() {
        return this.$store.state.context.psxId;
      },
      roundingSettingsIsCorrect() {
        return this.$store.getters.roundingSettingsIsCorrect;
      },
      debugLogsEnabled: {
        get() {
          return this.$store.state.configuration.debugLogsEnabled;
        },
        set(payload) {
          this.$store.dispatch('toggleDebugLogs', payload);
        },
      },
    },
  };
</script>
