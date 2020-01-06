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
  <b-card no-body>
    <b-card-body>
      <div class="m-auto max-width">
        <h1 class="title">{{ $t('block.reporting.title') }}</h1>
        <p class="subtitle">
          {{ $t('block.reporting.subtitle') }}.
          <a href="">{{ $t('block.reporting.subtitleLinkLabel') }}</a>
        </p>

        <b-table
          show-empty
          hover
          :items="orders"
          :fields="fields"
          :filter="filter"
          :current-page="currentPage"
          :per-page="perPage"
          @filtered="onFiltered"
        >
          <template v-slot:cell(user)="data">
            <a :href="`${data.item.userProfileLink}`">
              {{ data.item.username }}
            </a>
          </template>
          <template v-slot:cell(current_state)="data">
            <b-badge class="label color_field " :style="setBadgeColor(data.item.state.color)">
              {{ data.item.state.name }}
            </b-badge>
          </template>
          <template v-slot:cell(actions)="row">
            <a href="https://www.paypal.com/listing/transactions" target="_blank">
              {{ $t('block.reporting.gotopaypal') }}
            </a>
          </template>
        </b-table>

        <b-pagination
          v-model="currentPage"
          :total-rows="totalRows"
          :per-page="perPage"
          align="fill"
          size="sm"
          class="my-0"
        ></b-pagination>
      </div>
    </b-card-body>
  </b-card>
</template>

<script>
  import ajax from '@/requests/ajax.js';

  export default {
    name: 'Reporting',
    methods: {
      onFiltered(filteredItems) {
        this.totalRows = filteredItems.length;
        this.currentPage = 1;
      },
      getReportingDatas() {
        ajax({
          url: this.$store.getters.adminController,
          action: 'GetReportingDatas',
        }).then((response) => {
          this.orders = response.orders;
          this.totalRows = this.orders.length;
        });
      },
      setBadgeColor(color) {
        return {
          'background-color': color,
        };
      },
    },
    data() {
      return {
        filter: null,
        filterOn: [],
        orders: [],
        fields: [
          {key: 'date_add', label: 'Date', sortable: true},
          {key: 'id_order', label: 'Order ID', sortable: true},
          {key: 'user', label: 'Customer', sortable: true},
          {key: 'current_state', label: 'State', sortable: true},
          {key: 'before_commission', label: 'Before Commission', sortable: true},
          {key: 'commission', label: 'Commission', sortable: true},
          {key: 'total_paid', label: 'Total', sortable: true},
          {key: 'actions', label: 'Actions'},
        ],
        totalRows: 1,
        currentPage: 1,
        perPage: 20,
        pageOptions: [5, 10, 15],
      };
    },
    created() {
      this.getReportingDatas();
    },
  };
</script>

<style lang="scss" scoped>
.title {
  font-size: 26px;
}
.subtitle {
  font-size: 14px;
}
.label {
  border-radius: .25em;
}
</style>
