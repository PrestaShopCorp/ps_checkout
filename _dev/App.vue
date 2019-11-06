<template>
  <div id="app">
    <Menu>
      <MenuItem route="/authentication">{{ $t('menu.authentication') }}</MenuItem>
      <template v-if="onboardingPaypalIsCompleted && onboardingFirebaseIsCompleted">
        <MenuItem route="/customize">{{ $t('menu.customizeCheckout') }}</MenuItem>
        <MenuItem route="/activity">{{ $t('menu.manageActivity') }}</MenuItem>
        <MenuItem route="/advanced">{{ $t('menu.advancedSettings') }}</MenuItem>
      </template>
      <MenuItem route="/help">{{ $t('menu.help') }}</MenuItem>
    </Menu>

    <div class="pt-5" />

    <div class="row">
      <div class="container">
        <RoundingBanner />
      </div>
    </div>

    <div v-if="paymentMode === 'SANDBOX'" class="row">
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
  /**
    * TODO: Import prestaskit and try to make it work
    */
  // @import '~prestakit/scss/application';
}
</style>
