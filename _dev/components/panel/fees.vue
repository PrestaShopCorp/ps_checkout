<template>
  <form class="form form-horizontal">
    <div class="card">
      <div class="card-block row">
        <div class="card-text">
          <h1>How much fees will be taken on my sales from the processing service provider?</h1>

          <div class="row pt-4">
            <div class="col-12 col-sm-4 col-md-4 col-lg-4">
              <form class="form form-horizontal">
                <p>Calculate how much fees you will be paying when invoicing your clients.</p>
                <div class="form-group">
                  <label class="form-control-label">Your country of residence</label>
                  <select class="custom-select" v-model="toto">
                    <option v-for="(country, index) in fees" :key="index" :value="index">
                      {{ country.name }}
                    </option>
                  </select>
                </div>
                <div v-if="toto !== 'fr'" class="form-group">
                  <label class="form-control-label">Your estimated monthly sales</label>
                  <select class="custom-select">
                    <option v-for="(test, index) in fees[toto].paypalWallet" :key="index" :value="index">
                      <template>{{ index }}</template>
                    </option>
                  </select>
                </div>
              </form>
            </div>
            <div class="col-12 col-sm-8 col-md-8 col-lg-8">
              <table class="table">
                <thead>
                  <tr>
                    <th>Payment method</th>
                    <th>Fees</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th>PayPal</th>
                    <td>test 1</td>
                  </tr>
                  <tr>
                    <th>Visa, Mastercard, Discover</th>
                    <td>test 2</td>
                  </tr>
                  <tr>
                    <th>American Express</th>
                    <td>test 3</td>
                  </tr>
                  <tr>
                    <th>Local Payment Methods</th>
                    <td>test 4</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</template>

<script>
  export default {
    data() {
      return {
        toto: 'es',
      };
    },
    components: {
    },
    methods: {

    },
    computed: {
      fees() {
        return this.$store.state.configuration.module.fees;
      },
    },
  };
</script>

<style scoped>
.line-separator {
  height:1px;
  opacity: 0.2;
  background:#6B868F;
  border-bottom: 2px solid #6B868F;
}
</style>
