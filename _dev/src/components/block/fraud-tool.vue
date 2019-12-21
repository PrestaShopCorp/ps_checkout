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
  <div class="card container">
    <div class="card-block text-left py-4 px-5">
      <div class="m-auto max-width">
        <h1>{{ $t('block.fraud-tool.title') }}</h1>
        <p>
          {{ $t('block.fraud-tool.text') }}
        </p>
        <a
          href="https://www.paypal.com/fraud-protection"
          target="_blank"
        >
          {{ $t('block.fraud-tool.discoverFraudTool') }} <i class="material-icons">arrow_right_alt</i>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'FaudTool',
  };
</script>

<style scoped>
.card {
  width: fit-content;
}
.max-width {
  max-width: 500px !important;
}
</style>
