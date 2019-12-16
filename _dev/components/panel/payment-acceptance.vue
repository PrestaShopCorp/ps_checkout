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
  <form class="form form-horizontal">
    <div class="card">
      <h3 class="card-header">
        <i class="material-icons">credit_card</i>
        {{ $t('panel.payment-acceptance.paymentAcceptanceTitle') }}
      </h3>
      <div class="card-block row">
        <div class="card-text">
          <div class="row">
            <div class="pl-0">
              <img class="mb-3" src="@/assets/images/paypal-logo.png" width="100">
              <PaypalStatus :display-labels="true" />
            </div>
          </div>
          <div class="row d-block">
            <div class="line-separator my-3" />
          </div>
          <div class="row">
            <div class="pl-0">
              <h2>{{ $t('panel.payment-acceptance.creditCardsLabel') }}</h2>
              <CardStatus :display-labels="true" />
            </div>
          </div>
          <div class="row mt-2">
            <PSAlert :alert-type="ALERT_TYPE_INFO">
              <h2>{{ $t('panel.payment-acceptance.tips') }}</h2>
              <p>{{ $t('panel.payment-acceptance.alertInfo') }}</p>
            </PSAlert>
          </div>
        </div>
      </div>
    </div>
  </form>
</template>

<script>
  import CardStatus from '@/components/block/card-status';
  import PaypalStatus from '@/components/block/paypal-status';
  import PSAlert from '@/components/form/alert';
  import {ALERT_TYPE_INFO} from '@/lib/alert';

  export default {
    components: {
      CardStatus,
      PaypalStatus,
      PSAlert,
    },
    computed: {
      ALERT_TYPE_INFO: () => ALERT_TYPE_INFO,
    },
  };
</script>

<style scoped>
.line-separator {
  height:1px;
  opacity: 0.2;
  background:#6B868F;
  border-bottom: 2px solid #6B868F;
}
</style>
