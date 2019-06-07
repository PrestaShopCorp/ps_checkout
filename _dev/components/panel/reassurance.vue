<template>
  <div class="text-center p-4">
    <h2 class="text-muted">PrestaShop Payments, all-in-one module for your payment options</h2>
    <div class="container-fluid d-flex flex-wrap justify-content-center my-3">
      <div class="tip">
        <div class="img-tip mb-3">
          <img src="@/assets/images/card.png" alt="">
        </div>
        <div class="text-muted">
          <b>All payment methods</b> accept cards, PayPal and much more.
        </div>
      </div>
      <div class="tip">
        <div class="img-tip mb-3">
          <img src="@/assets/images/security.png" alt="">
        </div>
        <div class="text-muted">
          Benefit from all <b>PayPal expertise and advantages</b>
          (fraud prevention, secure technology, dispute resolution, …)
        </div>
      </div>
      <div class="tip">
        <div class="img-tip mb-3">
          <img src="@/assets/images/globe.png" alt="">
        </div>
        <div class="text-muted">
          Offer the most relevant <b>Local Payment Methods</b> to customers across the globe.
        </div>
      </div>
    </div>
    <div>
      <a :href="url" target="_blank">
        Learn more <i class="material-icons">arrow_right_alt</i>
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
        default: 'https:://www.prestashop.com',
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
