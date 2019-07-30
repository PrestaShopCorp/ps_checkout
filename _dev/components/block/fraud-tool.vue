<template>
  <div class="card container">
    <div class="card-block text-left py-4 px-5">
      <div class="m-auto max-width">
        <h1>{{ $t('block.fraud-tool.title') }}</h1>
        <p>
          {{ $t('block.fraud-tool.text') }}
        </p>
        <a href="https://www.paypal.com/fraud-protection" target="_blank">
          {{ $t('block.fraud-tool.discoverFraudTool') }} <i class="material-icons">arrow_right_alt</i>
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
