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
  <div class="card">
    <h3 class="card-header">
      <i class="material-icons">{{ icon }}</i>
      {{ iconTitle }}
    </h3>
    <b-card-body>
      <div class="ml-4 max-width">
        <h4>{{ title }}</h4>
        <p class="min-vh-100">
          {{ description }}
        </p>
        <b-button
          variant="primary"
          :href="linkUrl"
          target="_blank"
          class="mt-3"
          @click="onClickTrack(identifier)"
        >
          {{ linkTitle }}
        </b-button>
      </div>
    </b-card-body>
  </div>
</template>

<script>
  export default {
    name: 'AccountSettingsDeepLink',
    props: {
      icon: {
        type: String,
        required: true
      },
      iconTitle: {
        type: String,
        required: true
      },
      title: {
        type: String,
        required: true
      },
      description: {
        type: String,
        required: true
      },
      linkTitle: {
        type: String,
        required: true
      },
      linkUrl: {
        type: String,
        required: true
      },
      identifier: {
        type: String,
        required: true
      }
    },
    methods: {
      onClickTrack(identifier) {
        if ('fraud-tool' === identifier) {
          this.$segment.track('CKT Click Fraud Tool', {
            category: 'ps_checkout'
          });
        } else if ('bank-account' === identifier) {
          this.$segment.track('CKT Click Bank Account', {
            category: 'ps_checkout'
          });
        } else if ('currencies' === identifier) {
          this.$segment.track('CKT Click Manage Currencies', {
            category: 'ps_checkout'
          });
        } else if ('conversion-rules' === identifier) {
          this.$segment.track('CKT Click Conversion Rules', {
            category: 'ps_checkout'
          });
        } else if ('soft-descriptor' === identifier) {
          this.$segment.track('CKT Click Short Description', {
            category: 'ps_checkout'
          });
        }
      }
    }
  };
</script>

<style scoped>
  #app h3.card-header {
    background-color: transparent;
  }
  #app .card-body h4 {
    font-size: 16px;
  }
  #app .card-body p {
    min-height: 100px;
  }
</style>
