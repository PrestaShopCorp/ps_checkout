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
  <b-container class="m-auto">
    <b-row class="justify-content-center">
      <div>
        <img width="400" src="@/assets/images/pscheckout.png" alt="" />
      </div>
      <div>
        <h2 class="text-muted">
          {{ $t('block.reassurance.title') }}
        </h2>
        <ul class="pl-0 pt-2 text-muted">
          <li>
            <i class="material-icons">check</i>
            {{ $t('block.reassurance.label1') }}
          </li>
          <li>
            <i class="material-icons">check</i>
            {{ $t('block.reassurance.label2') }}
          </li>
          <li>
            <i class="material-icons">check</i>
            {{ $t('block.reassurance.label3') }}
          </li>
        </ul>
        <b-button variant="link" :href="url" target="_blank" class="pl-0">
          {{ $t('block.reassurance.learnMore') }}
          <i class="material-icons">arrow_right_alt</i>
        </b-button>
      </div>
    </b-row>
  </b-container>
</template>

<script>
  export default {
    props: {
      url: {
        type: String,
        required: false,
        default: 'https://www.prestashop.com/prestashop-checkout'
      }
    }
  };
</script>

<style scoped>
  ul {
    list-style-type: none;
  }
  li {
    margin: 0 0 8px 0;
  }
</style>
