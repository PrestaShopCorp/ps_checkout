<template>
  <div>
    <div class="row">
      <div class="container">
        <PaymentMode />
      </div>
    </div>
  </div>
</template>

<script>
  import PaymentMode from '@/components/panel/payment-mode';

  export default {
    name: 'Experimental',
    components: {
      PaymentMode,
    },
  };
</script>
