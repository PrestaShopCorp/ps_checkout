<template>
  <div class="card">
    <div class="card-block text-center py-4 px-5">
      <h1>0 {{ $t('block.dispute.pendingDispute') }}</h1>
      <a href="#" target="_blank">
        {{ $t('block.dispute.goToDispute') }} <i class="material-icons">arrow_right_alt</i>
      </a>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'Dispute',
  };
</script>

<style scoped>
.card {
  width: fit-content;
}
</style>
