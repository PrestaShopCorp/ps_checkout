<template>
  <div>
    <div class="row">
      <div class="container">
        <Dispute />
      </div>
    </div>
    <div class="row">
      <div class="container">
        <FeatureIncoming />
      </div>
    </div>
  </div>
</template>

<script>
  import Dispute from '@/components/block/dispute';
  import FeatureIncoming from '@/components/block/feature-incoming';

  export default {
    name: 'Activity',
    components: {
      Dispute,
      FeatureIncoming,
    },
  };
</script>
