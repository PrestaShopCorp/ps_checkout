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
  <div class="md-radio">
    <label>
      <input
        :id="id"
        :name="name"
        :for="id"
        type="radio"
        :value="toto"
        :class="{'indeterminate' : isIndeterminate }"
      >
      <i class="md-radio-control" />
      <slot />
    </label>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        isChecked: this.checked
      }
    },
    props: {
      id: {
        type: String,
        required: true,
      },
      name: {
        type: String,
        required: true,
      },
      toto: {
        type: String,
        required: true
      },
      isIndeterminate: {
        type: Boolean,
        required: false,
        default: false,
      },
      checked: {
        type: Boolean,
        required: false,
        default: false,
      }
    },
    watch: {
      checked(val) {
        this.$emit('input', val);
      },
    },
  };
</script>

<style scoped>
.md-radio {
  position: relative;
  margin: 0;
  margin: initial;
  text-align: left;
}
.md-radio input[type=radio] {
  outline: 0;
  display: none;
}
.md-radio label {
  margin-bottom: 0;
  padding-left: 25px;
}
i.md-radio-control:before {
  content: "";
  position: absolute;
  left: 0;
  top: 0;
  width: 20px;
  height: 20px;
  background: #fff;
  border: 2px solid #bbcdd2;
  border-radius: 50%;
  cursor: pointer;
  transition: background 0.3s;
}
i.md-radio-control:after {
  content: "";
  position: absolute;
  left: 0;
  top: 0;
}
input[type=radio].indeterminate + i.md-radio-control:after, input[type=radio]:checked + i.md-radio-control:after {
  background: #25b9d7;
  width: 10px;
  height: 10px;
  left: 5px;
  top: 5px;
  border-radius: 50%;
}
.md-radio input[type=radio].indeterminate+i.md-radio-control:before, .md-radio input[type=radio]:checked+i.md-radio-control:before {
  background: #fff;
  border: 2px solid #25b9d7;
}
</style>