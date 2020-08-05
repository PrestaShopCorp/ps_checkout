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
  <div id="app">
    <Menu>
      <MenuItem route="/authentication">
        {{ $t('menu.authentication') }}
      </MenuItem>
      <template
        v-if="onboardingPaypalIsCompleted && onboardingFirebaseIsCompleted"
      >
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
    <div class="pt-3" />

    <div class="container" v-if="isShopContext">
      <RoundingBanner />
    </div>

    <div class="container" v-if="isTestMode">
      <b-alert variant="warning" show>
        <p>{{ $t('general.testModeOn') }}</p>
      </b-alert>
    </div>

    <div class="container" v-if="!isShopContext">
      <b-alert variant="warning" show>
        <h2>{{ $t('general.multiShop.title') }}</h2>
        <p>{{ $t('general.multiShop.subtitle') }}</p>
        <p>{{ $t('general.multiShop.chooseOne') }}</p>
        <b-list-group
          v-for="group in shopsTree"
          :key="group.id"
          class="mt-3 mb-3 col-4"
        >
          <p class="text-muted">
            {{ $t('general.multiShop.group') }} {{ group.name }}
          </p>
          <b-list-group-item
            v-for="shop in group.shops"
            :key="shop.id"
            :href="shop.url"
          >
            {{ $t('general.multiShop.configure') }}
            <b>{{ shop.name }}</b>
          </b-list-group-item>
        </b-list-group>
        <p>{{ $t('general.multiShop.tips') }}</p>
      </b-alert>
    </div>

    <router-view v-if="isShopContext" />
  </div>
</template>

<script>
  import Menu from '@/components/menu/menu';
  import MenuItem from '@/components/menu/menu-item';
  import RoundingBanner from '@/components/block/rounding-banner';

  export default {
    name: 'Home',
    components: {
      Menu,
      MenuItem,
      RoundingBanner
    },
    data() {
      return {
        paypalStatusUpdater: null
      };
    },
    methods: {
      updater() {
        this.paypalStatusUpdater = setInterval(() => {
          this.$store.dispatch('refreshPaypalStatus');
        }, 10000);
      }
    },
    computed: {
      onboardingPaypalIsCompleted() {
        return this.$store.state.paypal.onboardingCompleted;
      },
      onboardingFirebaseIsCompleted() {
        return this.$store.state.firebase.onboardingCompleted;
      },
      accountIslinked() {
        return this.$store.state.paypal.accountIslinked;
      },
      isShopContext() {
        return this.$store.state.context.isShopContext;
      },
      isTestMode() {
        return (
          this.$store.state.configuration.paymentMode === 'SANDBOX' &&
          this.$store.state.context.isShopContext
        );
      },
      shopsTree() {
        return this.$store.state.context.shopsTree;
      }
    },
    watch: {
      accountIslinked(val) {
        if (!val && this.onboardingPaypalIsCompleted) {
          this.updater();
        } else {
          clearInterval(this.paypalStatusUpdater);
        }
      }
    },
    created() {
      if (!this.onboardingPaypalIsCompleted || this.accountIslinked) {
        return;
      }

      this.updater();
    }
  };
</script>

<style lang="scss">
  #app {
    @import '~bootstrap-vue/dist/bootstrap-vue';
    @import '~prestakit/dist/css/bootstrap-prestashop-ui-kit';
  }

  #app {
    margin: 0;
    font-family: Open Sans, Helvetica, Arial, sans-serif;
    font-size: 14px;
    font-size: 0.875rem;
    font-weight: 400;
    line-height: 1.5;
    color: #363a41;
    text-align: left;
  }

  #app .card-header,
  .card-header .card-header-title {
    font-weight: 600;
    line-height: 24px;
    line-height: 1.5rem;
  }

  #app .card-header .main-header #header-search-container .input-group:before,
  .card-header .material-icons,
  .card-header .ps-tree-items .tree-name button:before,
  .main-header #header-search-container .card-header .input-group:before,
  .ps-tree-items .tree-name .card-header button:before {
    color: #6c868e;
    margin-right: 5px;
  }

  #app .form-group.has-danger:after,
  #app .form-group.has-success:after,
  #app .form-group.has-warning:after {
    right: 10px;
  }

  .nobootstrap {
    background-color: unset !important;
    padding: 100px 10px 100px;
    min-width: unset !important;
  }

  .nobootstrap .form-group > div {
    float: unset;
  }

  .nobootstrap fieldset {
    background-color: unset;
    border: unset;
    color: unset;
    margin: unset;
    padding: unset;
  }

  .nobootstrap label {
    color: unset;
    float: unset;
    font-weight: unset;
    padding: unset;
    text-align: unset;
    text-shadow: unset;
    width: unset;
  }

  .nobootstrap .table tr th {
    background-color: unset;
    color: unset;
    font-size: unset;
  }

  .nobootstrap .table.table-hover tbody tr:hover {
    color: #fff;
  }

  .nobootstrap .table.table-hover tbody tr:hover a {
    color: #fff !important;
  }

  .nobootstrap .table tr td {
    border-bottom: unset;
    color: unset;
  }

  .nobootstrap .table {
    background-color: unset;
    border: unset;
    border-radius: unset;
    padding: unset;
  }

  .page-sidebar.mobile #content.nobootstrap {
    margin-left: unset;
  }

  .page-sidebar-closed:not(.mobile) #content.nobootstrap {
    padding-left: 50px;
  }

  .material-icons.js-mobile-menu {
    display: none !important;
  }

  @import url('https://fonts.googleapis.com/icon?family=Material+Icons');
</style>
