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
  <div v-if="!roundingSettingsIsCorrect && !isReady">
    <PSAlert :alert-type="ALERT_TYPE_WARNING">
      <h2>{{ $t('block.rounding-banner.title') }}</h2>
      <p class="mb-3">{{ $t('block.rounding-banner.content') }}</p>
      <PSButton ghost @click="updateRoundingSettings()">{{ $t('block.rounding-banner.button') }}</PSButton>
    </PSAlert>
  </div>
</template>

<script>
  import PSButton from '@/components/form/button';
  import {ALERT_TYPE_WARNING} from '@/lib/alert';
  import PSAlert from '@/components/form/alert';

  export default {
    name: 'RoundingBanner',
    components: {
      PSButton,
      PSAlert,
    },
    methods: {
      updateRoundingSettings() {
        this.$store.dispatch('updateRoundingSettings');
      },
    },
    computed: {
      isReady() {
        return this.$store.state.context.isReady;
      },
      roundingSettingsIsCorrect() {
        return this.$store.getters.roundingSettingsIsCorrect;
      },
      ALERT_TYPE_WARNING: () => ALERT_TYPE_WARNING,
    },
  };
</script>
