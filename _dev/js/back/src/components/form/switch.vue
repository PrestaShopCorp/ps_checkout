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
  <label :for="id">
    <label class="mr-2" v-if="textPosition === 'left'">
      <slot />
    </label>
    <div
      class="switch-input"
      :class="{
        '-checked': value,
        'switch-input-lg': size === 'lg',
        'switch-input-sm': size === 'sm'
      }"
    >
      <input
        :id="id"
        @input="$emit('input', $event.target.checked)"
        :checked="value"
        data-toggle="switch"
        data-inverse="true"
        type="checkbox"
        :name="name"
      />
    </div>
    <label class="ml-1" v-if="textPosition === 'right'">
      <slot />
    </label>
  </label>
</template>

<script>
  export default {
    name: 'PSSwitch',
    props: {
      value: {
        type: Boolean,
        required: false,
        default: false
      },
      id: {
        type: String,
        required: true
      },
      name: {
        type: String,
        required: false,
        default: ''
      },
      textPosition: {
        type: String,
        required: false,
        default: 'right'
      },
      size: {
        type: String,
        required: false,
        default: 'md'
      }
    },
    data() {
      return {
        isActive: this.value
      };
    },
    watch: {
      isActive(val) {
        this.$emit('input', val);
      }
    },
    methods: {
      toggleSwitch(value) {
        this.isActive = value;
      }
    }
  };
</script>
