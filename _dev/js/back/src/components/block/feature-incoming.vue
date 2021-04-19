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
  <div class="container-fluid feature-block p-5">
    <div class="feature-image">
      <img src="@/assets/images/brush.png" alt="" />
    </div>
    <div class="text-muted mt-4">
      {{ $t('block.feature-incoming.text') }}
    </div>
    <div class="mt-4">
      <b-button
        variant="link"
        :href="submitIdeaLink"
        target="_blank"
        class="ml-4 feedback-button"
      >
        {{ $t('block.feature-incoming.submitIdea') }}
      </b-button>
    </div>
  </div>
</template>

<style scoped>
  .feature-block {
    border: 2px dashed #dddddd;
    border-radius: 3px;
    text-align: center;
  }
  .text-muted {
    font-size: 16px;
  }
  .feature-block .feedback-button {
    width: 122px;
    height: 38px;
    border-radius: 6px !important;
    background-color: #3730a3 !important;
    color: #fff !important;
  }
</style>

<script>
  export default {
    computed: {
      submitIdeaLink() {
        return this.$store.state.context.submitIdeaLink;
      }
    }
  };
</script>
