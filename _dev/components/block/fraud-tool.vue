<template>
  <div class="card container">
    <div class="card-block text-left py-4 px-5">
      <div class="m-auto max-width">
        <h1>Fraud tool</h1>
        <p>
          PayPal algorithms limits your fraud rate automatically.
          If you want to go further in Fraud management, there is a complete
          tool on PayPal platform to set specific rules and drive your performance
          concerning fraud and chargeback costs.
        </p>
        <a href="#" target="_blank">
          Discover fraud tool <i class="material-icons">arrow_right_alt</i>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'FaudTool',
  };
</script>

<style scoped>
.card {
  width: fit-content;
}
.max-width {
  max-width: 500px !important;
}
</style>
