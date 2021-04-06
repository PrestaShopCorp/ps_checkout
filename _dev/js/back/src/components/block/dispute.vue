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
  <b-card no-body>
    <b-card-body>
      <b-row>
        <b-col>
          <b-button variant="text" size="lg">
            <i class="material-icons">school</i>
            {{ $t('block.dispute.pendingDispute', { disputeCount: 0 }) }}
          </b-button>
        </b-col>
        <b-col cols="12" md="auto" class="m-auto">
          <b-button
            variant="link"
            href="https://www.paypal.com/disputes/"
            target="_blank"
            @click="onClickViewPayPalDisputes()"
          >
            {{ $t('block.dispute.goToDispute') }}
            <i class="material-icons">arrow_right_alt</i>
          </b-button>
        </b-col>
      </b-row>
    </b-card-body>
  </b-card>
</template>

<script>
  export default {
    name: 'Dispute',
    methods: {
      onClickViewPayPalDisputes() {
        this.$segment.track('CKT Click PayPal Disputes', {
          category: 'ps_checkout'
        });
      }
    }
  };
</script>
