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
  <b-card
    id="reporting"
    no-body
  >
    <b-card-body>
      <div class="m-auto max-width">
        <h1 class="title">
          {{ $t('block.reporting.title') }}
        </h1>
        <p class="subtitle">
          {{ $t('block.reporting.label') }}.
          <a
            href="https://www.paypal.com/listing/transactions"
            target="_blank"
          >
            {{ $t('block.reporting.subtitleLinkLabel') }}
          </a>
        </p>

        <h2 class="mt-4">
          {{ this.orders.length }} {{ $t('block.reporting.subTitle1') }}
        </h2>

        <!-- Orders table -->
        <b-table
          show-empty
          hover
          :items="orders"
          :fields="orderFields"
          :filter="orderFilter"
          :current-page="orderCurrentPage"
          :per-page="orderPerPage"
          @filtered="onOrderFiltered"
          @row-clicked="onRowClicked"
        >
          <template v-slot:cell(user)="data">
            <a :href="`${data.item.userProfileLink}`">
              {{ data.item.username }}
            </a>
          </template>
          <template v-slot:cell(current_state)="">
            <b-badge
              class="label color_field"
              style="background-color:#3498DB"
            >
              {{ $t('block.reporting.pending') }}
            </b-badge>
          </template>
          <template v-slot:cell(actions)="">
            <a
              href="https://www.paypal.com/listing/transactions"
              target="_blank"
            >
              {{ $t('block.reporting.goToPaypal') }}
            </a>
          </template>
        </b-table>

        <h2 class="mt-4">
          {{ this.transactions.length }} {{ $t('block.reporting.subTitle2') }}
        </h2>

        <!-- Transactions table -->
        <b-table
          show-empty
          hover
          :items="transactions"
          :fields="transactionFields"
          :filter="transactionFilter"
          :current-page="transactionCurrentPage"
          :per-page="transactionPerPage"
          @filtered="onTransactionFiltered"
          @row-clicked="onRowClicked"
        >
          <template v-slot:cell(user)="data">
            <a
              :href="`${data.item.userProfileLink}`"
              target="_blank"
            >
              {{ data.item.username }}
            </a>
          </template>

          <template v-slot:cell(type)="data">
            <b-badge
              class="label color_field "
              :style="setTransactionBadgeColor(data.item.type)"
            >
              {{ data.item.type }}
            </b-badge>
          </template>

          <template v-slot:cell(actions)="data">
            <a
              :href="`https://www.paypal.com/activity/payment/${data.item.transactionID}`"
              target="_blank"
            >
              {{ $t('block.reporting.goToTransaction') }}
            </a>
          </template>
        </b-table>

        <b-pagination
          v-model="transactionCurrentPage"
          :total-rows="transactionTotalRows"
          :per-page="transactionPerPage"
          align="fill"
          class="my-0 m-auto w-fit-content"
        />
      </div>
    </b-card-body>
  </b-card>
</template>

<script>
  import ajax from '@/requests/ajax.js';

  export default {
    name: 'Reporting',
    methods: {
      onRowClicked(item) {
        window.open(item.orderLink);
      },
      onOrderFiltered(filteredItems) {
        this.orderTotalRows = filteredItems.length;
        this.orderCurrentPage = 1;
      },
      onTransactionFiltered(filteredItems) {
        this.transactionTotalRows = filteredItems.length;
        this.transactionCurrentPage = 1;
      },
      getReportingDatas() {
        ajax({
          url: this.$store.getters.adminController,
          action: 'GetReportingDatas',
        }).then((response) => {
          this.orders = response.orders;
          this.transactions = response.transactions;
          this.orderTotalRows = this.orders.length;
          this.transactionTotalRows = this.transactions.length;
          this.countAllCheckoutTransactions = response.countAllCheckoutTransactions;
        });
      },
      setBadgeColor(color) {
        return {
          'background-color': color,
        };
      },
      setTransactionBadgeColor(type) {
        let color = '#00B887';

        if (type === 'Refund') {
          color = '#34219E';
        }

        return {
          'background-color': color,
        };
      },
    },
    data() {
      return {
        orderFields: [
          {key: 'date_add', label: 'Date', sortable: true},
          {key: 'id_order', label: 'Order ID', sortable: true},
          {key: 'user', label: 'Customer', sortable: true},
          {key: 'current_state', label: 'Type', sortable: true},
          {key: 'before_commission', label: 'Before Commission', sortable: true},
          {key: 'commission', label: 'Commission', sortable: true},
          {key: 'total_paid', label: 'Total', sortable: true},
          {key: 'actions', label: 'Actions'},
        ],
        orderFilter: null,
        orderFilterOn: [],
        orders: [],
        orderTotalRows: 1,
        orderCurrentPage: 1,
        orderPerPage: 20,
        orderPageOptions: [5, 10, 15],
        transactionFields: [
          {key: 'date_add', label: 'Date', sortable: true},
          {key: 'order_id', label: 'Order ID', sortable: true},
          {key: 'user', label: 'Customer', sortable: true},
          {key: 'type', label: 'Type', sortable: true},
          {key: 'before_commission', label: 'Before Commission', sortable: true},
          {key: 'commission', label: 'Commission', sortable: true},
          {key: 'total_paid', label: 'Total', sortable: true},
          {key: 'actions', label: 'Actions'},
        ],
        transactionFilter: null,
        transactionFilterOn: [],
        transactions: [],
        transactionTotalRows: 1,
        transactionCurrentPage: 1,
        transactionPerPage: 20,
        transactionPageOptions: [5, 10, 15],
        countAllCheckoutTransactions: null,
      };
    },
    created() {
      this.getReportingDatas();
    },
  };
</script>

<style scoped>
.title {
  font-size: 26px;
}
.subtitle {
  font-size: 14px;
}
.label {
  border-radius: .25em;
}
.w-fit-content {
  width: fit-content;
}
</style>

<style>
#app #reporting .table tr:focus {
  outline: unset;
}
</style>
