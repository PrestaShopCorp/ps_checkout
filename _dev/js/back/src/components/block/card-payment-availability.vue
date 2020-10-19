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
    <template v-if="available">
      <b-badge variant="success">
        {{ $t('panel.active-payment.available') }}
      </b-badge>
    </template>
    <template v-else-if="restricted">
      <b-badge variant="warning">
        {{ $t('panel.active-payment.restricted') }}
      </b-badge>
    </template>
    <template v-else>
      <b-badge variant="danger">
        {{ $t('panel.active-payment.notAvailable') }}
      </b-badge>
    </template>
  </div>
</template>

<script>
  export default {
    name: 'CardPaymentAvailability',
    computed: {
      available() {
        return (
          this.$store.state.paypal.emailIsValid &&
          this.$store.state.paypal.paypalIsActive &&
          this.$store.state.paypal.cardIsActive === 'SUBSCRIBED'
        );
      },
      restricted() {
        return this.$store.state.paypal.cardIsActive === 'LIMITED';
      }
    }
  };
</script>
