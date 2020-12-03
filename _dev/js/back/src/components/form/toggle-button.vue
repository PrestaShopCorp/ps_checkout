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
  <div>
    <div
      class="btn-group btn-toggle"
      @click="$emit('click', $event.target.value)"
    >
      <button
        v-for="option in options"
        :key="option.name"
        class="btn btn-lg"
        :class="{
          'btn-default': !option.value,
          'btn-primary': option.value,
          active: option.value
        }"
        :value="option.name"
      >
        {{ option.label }}
      </button>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'PSToggleButton',
    props: {
      options: {
        type: Array,
        required: true,
        default: function() {
          return [];
        }
      }
    },
    mounted() {
      var btnToggle = document.querySelector('.btn-toggle');

      btnToggle.addEventListener('click', function() {
        var btns = this.querySelectorAll('.btn');

        for (var i = 0; i < btns.length; i++) {
          btns[i].classList.toggle('active');
          btns[i].classList.toggle('btn-default');
          btns[i].classList.toggle('btn-primary');
        }
      });
    }
  };
</script>
