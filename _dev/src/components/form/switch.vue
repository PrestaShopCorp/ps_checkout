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
  <div class="mod-switch">
    <label
      :class="{ active : !isActive }"
      @click="toggleSwitch(false)"
    >{{ leftText }}</label>
    <label
      :class="{ active : isActive }"
      @click="toggleSwitch(true)"
    >{{ rightText }}</label>
    <span
      class="switch-slide"
      :class="{ active : isActive }"
    />
  </div>
</template>

<script>
  export default {
    name: 'PowerSwitch',
    props: {
      value: Boolean,
      leftText: {
        type: String,
        required: true,
      },
      rightText: {
        type: String,
        required: true,
      },
    },
    data() {
      return {
        isActive: this.value,
      };
    },
    watch: {
      isActive(val) {
        this.$emit('input', val);
      },
    },
    methods: {
      toggleSwitch(value) {
        this.isActive = value;
      },
    },
  };
</script>

<style scoped lang="scss">
  .mod-switch {
    position: relative;
    display: inline-block;
    min-width: 130px;
    min-width: 8.125rem;
    margin-bottom: 1px;
    font-size: 13.008px;
    font-size: .813rem;
    background-color: #eff1f2;
    border-radius: 1px;
    text-align: center;
    vertical-align: middle;
  }
  .mod-switch label {
    position: relative;
    z-index: 2;
    float: left;
    width: 50%;
    height: 100%;
    padding: .231em .769em;
    margin: 0;
    line-height: 19px;
    vertical-align: middle;
    text-align: center;
    text-transform: uppercase;
    color: #bbcdd2;
    font-weight: 400;
    cursor: pointer;
    transition: color .2s ease-out;
  }
  .mod-switch .active {
    color: #fff;
  }
  .switch-slide {
    position: absolute;
    top: 0;
    left: 0;
    padding: 0;
    z-index: 1;
    width: 50%;
    height: 100%;
    color: #fff;
    background-color: #c05c67;
    transition: all .3s ease-out;
  }
  .switch-slide.active {
    background-color: #2eacce;
    left: 50%;
  }
</style>
