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
  <div
    id="rateThisModule"
    v-if="showModuleRate"
  >
    <p>
      <img
        src="@/assets/images/star_img.png"
        alt="Shining Star"
      >
      {{ $t('block.rate.firstSentence') }}
      <a
        target="_blank"
        :href="'https://addons.prestashop.com/' + currentIsoLang + '/ratings.php'"
      >
        {{ $t('block.rate.secondSentence') }}
      </a>
    </p>
  </div>
</template>

<script>
  export default {
    name: 'RateModule',
    computed: {
      currentIsoLang() {
        return this.$store.state.context.language.iso_code;
      },
      showModuleRate() {
        return this.$store.state.context.showModuleRate;
      },
    },
  };
</script>

<style scoped lang="scss">
  #rateThisModule{
    margin-top: 25px;
    p {
      text-align: right;
      font-size: 14px;
    }
    .material-icons {
      font-size: 14px;
      margin-left:10px;
      vertical-align: middle;
    }
    img {
      vertical-align: baseline;
    }
    a {
      font-weight: 600;
    }
  }
</style>
