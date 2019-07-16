<template>
  <div class="text-center p-4">
    <h2 class="text-muted">{{ $t('block.reassurance.title') }}</h2>
    <div class="container-fluid d-flex flex-wrap justify-content-center my-3">
      <div class="tip">
        <div class="img-tip mb-3">
          <img src="@/assets/images/card.png" alt="">
        </div>
        <div class="text-muted">
          <b>{{ $t('block.reassurance.firstTip1') }}</b> {{ $t('block.reassurance.firstTip2') }}
        </div>
      </div>
      <div class="tip">
        <div class="img-tip mb-3">
          <img src="@/assets/images/security.png" alt="">
        </div>
        <div class="text-muted">
          {{ $t('block.reassurance.secondTip1') }} <b>{{ $t('block.reassurance.secondTip2') }}</b>
          {{ $t('block.reassurance.secondTip3') }}
        </div>
      </div>
      <div class="tip">
        <div class="img-tip mb-3">
          <img src="@/assets/images/globe.png" alt="">
        </div>
        <div class="text-muted">
          {{ $t('block.reassurance.thirdTip1') }} <b>{{ $t('block.reassurance.thirdTip2') }}</b> {{ $t('block.reassurance.thirdTip3') }}
        </div>
      </div>
    </div>
    <div>
      <a :href="url" target="_blank">
        {{ $t('block.reassurance.learnMore') }} <i class="material-icons">arrow_right_alt</i>
      </a>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      url: {
        type: String,
        required: false,
        default: 'https://www.prestashop.com/prestashop-checkout',
      },
    },
  };
</script>

<style scoped>
  .tip {
    width: 350px;
    padding: 25px;
  }
  .img-tip {
    height: 55px;
  }
</style>
