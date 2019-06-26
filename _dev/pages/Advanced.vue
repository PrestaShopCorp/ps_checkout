<template>
  <div>
    <div class="row">
      <div class="container">
        <PaymentMode />
      </div>
    </div>
    <div class="row">
      <div class="container">
        <FraudTool />
      </div>
    </div>
  </div>
</template>

<script>
  import PaymentMode from '@/components/panel/payment-mode';
  import FraudTool from '@/components/block/fraud-tool';

  export default {
    name: 'Advanced',
    components: {
      PaymentMode,
      FraudTool,
    },
  };
</script>
