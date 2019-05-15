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
  <div class="card block-reassurance p-4">
    <p class="title">PrestaShop Payments, all-in-one module for your payment options</p>
    <ul class="no-bullet mt-4 mb-0">
      <li class="first-li"><b>All payment methods</b> accept cards, PayPal and much more.</li>
      <li class="second-li">Benefit from all <b>PayPal expertise and advantages</b> (fraud prevention, secure technology, dispute resolution, …)</li>
      <li class="third-li">Offer the most relevant <b>Local Payment Methods</b> to customers across the globe</li>
    </ul>
    <div class="learn-more">
      <a class="card-link" :href="url" target="_blank">Learn more <i class="material-icons">arrow_right_alt</i></a>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    url: {
      type: String,
      required: false,
      default: 'https:://www.prestashop.com',
    },
  },
};
</script>

<style scoped>
.no-bullet {
  list-style-type: none;
  padding: 0;
}
.title {
  color: #363A41;
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
}
li {
  color: #6C868E;
  font-size: 14px;
  line-height: 21px;
  padding-left: 44px;
  padding-bottom: 30px;
}
.first-li {
  background: url('../../assets/images/card.png') no-repeat left top;
}
.second-li {
  background: url('../../assets/images/security.png') no-repeat left top;
}
.third-li {
  background: url('../../assets/images/globe.png') no-repeat left top;
}
.learn-more {
  text-align: center;
}
.block-reassurance {
  width: 400px;
  min-width: 400px;
}
</style>
