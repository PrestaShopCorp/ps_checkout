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
