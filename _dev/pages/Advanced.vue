<template>
  <div>
    <div class="row">
      <div class="container">
        <FraudTool />
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
  import FraudTool from '@/components/block/fraud-tool';
  import FeatureIncoming from '@/components/block/feature-incoming';

  export default {
    name: 'Advanced',
    components: {
      FraudTool,
      FeatureIncoming,
    },
  };
</script>
