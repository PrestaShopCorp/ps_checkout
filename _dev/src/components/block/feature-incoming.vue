<!--**
 * 2007-2020 PrestaShop and Contributors
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
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *-->
<template>
  <div class="container-fluid feature-block p-5">
    <div class="feature-image">
      <img src="@/assets/images/brush.png">
    </div>
    <div class="text-muted mt-4">
      {{ $t('block.feature-incoming.text') }}
    </div>
  </div>
</template>

<style scoped>
  .feature-block {
    border: 2px dashed #DDDDDD;
    border-radius: 3px;
    text-align: center;
  }
  .text-muted {
    font-size: 16px;
  }
</style>
