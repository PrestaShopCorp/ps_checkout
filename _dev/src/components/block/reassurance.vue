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
  <div class="text-center p-4">
    <h2 class="text-muted">
      {{ $t('block.reassurance.title') }}
    </h2>
    <div class="container-fluid d-flex flex-wrap justify-content-center my-3">
      <div class="tip">
        <div class="img-tip mb-3">
          <img
            src="@/assets/images/card.png"
            alt=""
          >
        </div>
        <div class="text-muted">
          <b>{{ $t('block.reassurance.firstTip1') }}</b> {{ $t('block.reassurance.firstTip2') }}
        </div>
      </div>
      <div class="tip">
        <div class="img-tip mb-3">
          <img
            src="@/assets/images/security.png"
            alt=""
          >
        </div>
        <div class="text-muted">
          {{ $t('block.reassurance.secondTip1') }} <b>{{ $t('block.reassurance.secondTip2') }}</b>
          {{ $t('block.reassurance.secondTip3') }}
        </div>
      </div>
      <div class="tip">
        <div class="img-tip mb-3">
          <img
            src="@/assets/images/globe.png"
            alt=""
          >
        </div>
        <div class="text-muted">
          {{ $t('block.reassurance.thirdTip1') }}
          <b>{{ $t('block.reassurance.thirdTip2') }}</b>
          {{ $t('block.reassurance.thirdTip3') }}
        </div>
      </div>
    </div>
    <div>
      <a
        :href="url"
        target="_blank"
      >
        {{ $t('block.reassurance.learnMore') }} <i class="material-icons">arrow_right_alt</i>
      </a>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      url: {
        type: String,
        required: false,
        default: 'https://www.prestashop.com/prestashop-checkout',
      },
    },
  };
</script>

<style scoped>
  .tip {
    width: 350px;
    padding: 25px;
  }
  .img-tip {
    height: 55px;
  }
</style>
