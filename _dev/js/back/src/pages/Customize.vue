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
  <div>
    <b-container class="mb-4">
      <ActivePayment />
    </b-container>

    <b-container v-if="shopIs17" class="mb-4">
      <ExpressCheckout />
    </b-container>

    <b-container class="container">
      <FeatureIncoming />
    </b-container>
  </div>
</template>

<script>
import ActivePayment from "@/components/panel/active-payment";
import ExpressCheckout from "@/components/panel/express-checkout";
import FeatureIncoming from "@/components/block/feature-incoming";

export default {
  name: "Customize",
  components: {
    ActivePayment,
    ExpressCheckout,
    FeatureIncoming
  },
  computed: {
    shopIs17() {
      return this.$store.getters.shopIs17;
    }
  }
};
</script>
