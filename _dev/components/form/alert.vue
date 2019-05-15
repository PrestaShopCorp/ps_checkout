<!--**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *-->
<template>
  <div class="ps-alert alert" :class="classObject" role="alert">
    <button
      v-if="hasClose"
      type="button"
      class="close"
      data-dismiss="alert"
      aria-label="Close"
      @click.stop="closeAlert()"
    >
      <span class="material-icons">close</span>
    </button>
    <p class="alert-text">
      <slot />
    </p>
  </div>
</template>

<script>
import * as types from '@/lib/alert';

export default {
  props: {
    alertType: {type: String, required: true},
    hasClose: {type: Boolean, required: true},
  },
  computed: {
    classObject() {
      return {
        'alert-info': this.alertType === types.ALERT_TYPE_INFO,
        'alert-warning': this.alertType === types.ALERT_TYPE_WARNING,
        'alert-danger': this.alertType === types.ALERT_TYPE_DANGER,
        'alert-success': this.alertType === types.ALERT_TYPE_SUCCESS,
      };
    },
  },
  methods: {
    closeAlert() {
      this.$emit('closeAlert');
    },
  },
};
</script>
