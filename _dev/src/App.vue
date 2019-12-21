<!--**
 * 2007-2019 PrestaShop and Contributors
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
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *-->
<template>
  <div id="app">
    <Menu>
      <MenuItem route="/authentication">
        {{ $t('menu.authentication') }}
      </MenuItem>
      <template v-if="onboardingPaypalIsCompleted && onboardingFirebaseIsCompleted">
        <MenuItem route="/customize">
          {{ $t('menu.customizeCheckout') }}
        </MenuItem>
        <MenuItem route="/activity">
          {{ $t('menu.manageActivity') }}
        </MenuItem>
        <MenuItem route="/advanced">
          {{ $t('menu.advancedSettings') }}
        </MenuItem>
      </template>
      <MenuItem route="/help">
        {{ $t('menu.help') }}
      </MenuItem>
    </Menu>

    <div class="pt-5" />

    <div class="row">
      <div class="container">
        <RoundingBanner />
      </div>
    </div>

    <div
      v-if="paymentMode === 'SANDBOX'"
      class="row"
    >
      <div class="container">
        <PSAlert :alert-type="ALERT_TYPE_WARNING">
          <p>{{ $t('general.testModeOn') }}</p>
        </PSAlert>
      </div>
    </div>
    <router-view />
  </div>
</template>

<script>
  import Menu from '@/components/menu/menu';
  import MenuItem from '@/components/menu/menu-item';
  import PSAlert from '@/components/form/alert';
  import RoundingBanner from '@/components/block/rounding-banner';
  import {ALERT_TYPE_WARNING} from '@/lib/alert';

  export default {
    name: 'Home',
    components: {
      Menu,
      MenuItem,
      PSAlert,
      RoundingBanner,
    },
    computed: {
      ALERT_TYPE_WARNING: () => ALERT_TYPE_WARNING,
      onboardingPaypalIsCompleted() {
        return this.$store.state.paypal.onboardingCompleted;
      },
      onboardingFirebaseIsCompleted() {
        return this.$store.state.firebase.onboardingCompleted;
      },
      paymentMode() {
        return this.$store.state.configuration.paymentMode;
      },
    },
  };
</script>

<style lang="scss">
  #app {
    @import '~bootstrap-vue/dist/bootstrap-vue';
    @import '~prestakit/dist/css/bootstrap-prestashop-ui-kit';
  }

  .nobootstrap {
    background-color: unset !important;
  }

  .material-icons.js-mobile-menu {
    display: none !important
  }

  @import url('https://fonts.googleapis.com/icon?family=Material+Icons');
</style>
