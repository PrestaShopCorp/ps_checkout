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
        <i class="material-icons">credit_card</i> {{ $t('panel.payment-acceptance.paymentAcceptanceTitle') }}
      </h3>

      <div class="card-body m-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Payment method</th>
              <th>Availability</th>
              <th>Activation status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>

              </td>
              <td>test</td>
              <td>test</td>
            </tr>

            <tr>
              <td>test</td>
              <td>test</td>
              <td>test</td>
            </tr>
          </tbody>
        </table>

        <div class="pl-0">
          <img
            class="mb-3"
            src="@/assets/images/paypal-logo.png"
            width="100"
          >
          <PaypalStatus :display-labels="true" />
        </div>

        <div class="d-block">
          <div class="line-separator my-3" />
        </div>

        <div class="pl-0">
          <h2>{{ $t('panel.payment-acceptance.creditCardsLabel') }}</h2>
          <CardStatus :display-labels="true" />
        </div>

        <b-alert
          class="mt-2"
          variant="info"
          show
        >
          <h4 class="alert-heading">
            {{ $t('panel.payment-acceptance.tips') }}
          </h4>
          <p>{{ $t('panel.payment-acceptance.alertInfo') }}</p>
        </b-alert>
      </div>
    </div>
  </form>
</template>

<script>
  import CardStatus from '@/components/block/card-status';
  import PaypalStatus from '@/components/block/paypal-status';

  export default {
    components: {
      CardStatus,
      PaypalStatus,
    },
  };
</script>

<style scoped>
.nobootstrap .table {
    border: unset;
    border-radius: unset;
}

.nobootstrap .table tr th {
    background-color: white;
}

.line-separator {
  height:1px;
  opacity: 0.2;
  background:#6B868F;
  border-bottom: 2px solid #6B868F;
}
</style>
