<template>
  <div class="card">
    <h3 class="card-header">
      <i class="material-icons">toggle_on</i> Active payment methods
    </h3>
    <div class="card-block">
      <div class="d-flex m-auto payment-method-container">
        <div class="col-2 number">
          <div>1</div>
          <div>2</div>
        </div>
        <div class="col-10 payment-block">
          <div class="payment-method">
            <!-- <i v-handle class="material-icons handle">drag_indicator</i> -->
            temp
          </div>
          <div class="payment-method">
            <!-- <i v-handle class="material-icons handle">drag_indicator</i> -->
            temp
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    components: {

    },
    data() {
      return {
        slides: [],
      };
    },
    methods: {
      updatePosition(payload) {
        console.log(payload);
      },
    },
  };
</script>

<style scoped>
  .test {
    text-align: center;
  }
  .payment-method-container {
    width: 500px;
  }
  .payment-method {
    position: relative;
    display: block;
    margin-bottom: 10px;
    margin-bottom: .625rem;
    background-color: #fff;
    border: 1px solid #dddddd;
    border-radius: 3px;
    max-width: 475px;
    height: 60px;
  }
  .payment-block {

  }
  .number {
    text-align: right;
  }
</style>
