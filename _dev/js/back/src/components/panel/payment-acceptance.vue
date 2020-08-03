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
  <form class="form form-horizontal">
    <div class="card">
      <h3 class="card-header">
        <i class="material-icons">credit_card</i>
        {{ $t("panel.payment-acceptance.paymentAcceptanceTitle") }}
      </h3>

      <div class="card-body m-auto">
        <b-col sm="12" md="10" lg="10" class="m-auto">
          <table class="table text-center">
            <thead>
              <tr>
                <th class="text-left">
                  {{ $t("panel.payment-acceptance.paymentMethod") }}
                </th>
                <th>{{ $t("panel.payment-acceptance.availability") }}</th>
                <th>{{ $t("panel.payment-acceptance.activationStatus") }}</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="text-left d-flex">
                  <div class="paypal-icon">
                    <img
                      src="@/assets/images/paypal-logo-thumbnail.png"
                      alt=""
                    />
                  </div>
                  <div>
                    <h3 class="mt-2">
                      PayPal
                    </h3>
                    <PaypalStatus :display-labels="true" />
                  </div>
                </td>
                <td>
                  <PayPalPaymentAvailability />
                </td>
                <td>
                  <PayPalPaymentActivation />
                </td>
              </tr>

              <tr>
                <td class="text-left d-flex">
                  <div class="card-icon">
                    <i class="material-icons">credit_card</i>
                  </div>
                  <div>
                    <h3 class="mt-2">
                      {{ $t("panel.payment-acceptance.creditCardsLabel") }}
                    </h3>
                    <CardStatus :display-labels="true" />
                  </div>
                </td>
                <td>
                  <CardPaymentAvailability />
                </td>
                <td>
                  <CardPaymentActivation />
                </td>
              </tr>
            </tbody>
          </table>

          <b-alert class="mt-2" variant="info" show>
            <h4 class="alert-heading">
              {{ $t("panel.payment-acceptance.tips") }}
            </h4>
            <p>{{ $t("panel.payment-acceptance.alertInfo") }}</p>
          </b-alert>
        </b-col>
      </div>
    </div>
  </form>
</template>

<script>
import CardPaymentActivation from "@/components/block/card-payment-activation.vue";
import CardPaymentAvailability from "@/components/block/card-payment-availability.vue";
import CardStatus from "@/components/block/card-status";
import PayPalPaymentActivation from "@/components/block/paypal-payment-activation.vue";
import PayPalPaymentAvailability from "@/components/block/paypal-payment-availability.vue";
import PaypalStatus from "@/components/block/paypal-status";

export default {
  components: {
    CardPaymentActivation,
    CardPaymentAvailability,
    CardStatus,
    PayPalPaymentActivation,
    PayPalPaymentAvailability,
    PaypalStatus
  }
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
  height: 1px;
  opacity: 0.2;
  background: #6b868f;
  border-bottom: 2px solid #6b868f;
}

.paypal-icon {
  align-self: center;
  padding: 15px;
}

.card-icon {
  align-self: center;
  padding: 15px;
}

.card-icon .material-icons {
  font-size: 42px !important;
  color: #6c868e;
}
</style>
