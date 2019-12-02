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
  <button type="button" class="btn" :class="classObject" @click="onClick">
    <slot />
  </button>
</template>

<script>
  export default {
    name: 'PsButton',
    props: {
      primary: {
        type: Boolean,
      },
      ghost: {
        type: Boolean,
      },
    },
    computed: {
      classObject() {
        if (this.ghost) {
          return {
            'btn-outline-primary': this.primary,
            'btn-outline-secondary': !this.primary,
          };
        }

        return {
          'btn-primary': this.primary,
          'btn-secondary': !this.primary,
        };
      },
    },
    methods: {
      onClick() {
        this.$emit('click');
      },
    },
  };
</script>
