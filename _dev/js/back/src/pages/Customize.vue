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
  <div>
    <b-container class="mb-4">
      <b-alert variant="warning" :show="shopIsUsingCustomTheme">
        <p>
          {{ $t('pages.customize.customThemeWarningMessage1') }}
          <a
            href="https://github.com/PrestaShopCorp/ps_checkout/wiki"
            target="_blank"
            class="link-underline"
          >
            {{ $t('pages.customize.customThemeWarningMessage2') }}
          </a>.
        </p>
      </b-alert>
    </b-container>

    <b-container class="mb-4">
      <ActivePayment />
    </b-container>

    <b-container v-if="payIn4XActiveForMerchant" class="mb-4">
      <PayIn4X />
    </b-container>

    <b-container class="mb-4">
      <ExpressCheckout />
    </b-container>

    <b-container class="mb-4">
      <ButtonCustomization />
    </b-container>

    <b-container class="container">
      <FeatureIncoming />
    </b-container>
  </div>
</template>

<script>
  import ActivePayment from '@/components/panel/active-payment';
  import ExpressCheckout from '@/components/panel/express-checkout';
  import PayIn4X from '@/components/panel/pay-in-4x';
  import ButtonCustomization from '@/components/panel/button-customization';
  import FeatureIncoming from '@/components/block/feature-incoming';

  export default {
    name: 'Customize',
    components: {
      ActivePayment,
      PayIn4X,
      ExpressCheckout,
      ButtonCustomization,
      FeatureIncoming
    },
    computed: {
      shopIs17() {
        return this.$store.getters.shopIs17;
      },
      payIn4XActiveForMerchant() {
        return this.$store.state.configuration.payIn4X.activeForMerchant;
      },
      shopIsUsingCustomTheme() {
        return this.$store.state.context.isCustomTheme;
      }
    }
  };
</script>

<style scoped>
  #app a.link-underline {
    color: inherit;
    text-decoration: underline;
    font-weight: bold;
  }
</style>
