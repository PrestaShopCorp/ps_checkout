
<template>
  <div>
    <div class="row">
      <div class="container">
        <FeesPanel />
      </div>
    </div>
  </div>
</template>

<script>
  import FeesPanel from '@/components/panel/fees';

  export default {
    name: 'Fees',
    components: {
      FeesPanel,
    },
  };
</script>
