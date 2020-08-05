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
    <template v-if="cardIsAvailable">
      <b-badge variant="success">
        {{ $t('panel.active-payment.enabled') }}
      </b-badge>
    </template>
    <template v-else>
      <b-badge variant="danger">
        {{ $t('panel.active-payment.disabled') }}
      </b-badge>
    </template>
  </div>
</template>

<script>
  export default {
    name: 'CardPaymentActivation',
    computed: {
      cardIsAvailable() {
        return (
          this.$store.state.paypal.emailIsValid &&
          this.$store.state.paypal.paypalIsActive &&
          this.$store.state.configuration.cardIsEnabled &&
          (this.$store.state.paypal.cardIsActive === 'SUBSCRIBED' ||
            this.$store.state.paypal.cardIsActive === 'LIMITED')
        );
      }
    }
  };
</script>
