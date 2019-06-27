<template>
  <form class="form form-horizontal">
    <div class="card">
      <div class="card-block row">
        <div class="card-text">
          <h1>{{ $t('panel.fees.title') }}</h1>

          <div class="row pt-4">
            <div class="col-12 col-sm-4 col-md-4 col-lg-4">
              <form class="form form-horizontal">
                <p>{{ $t('panel.fees.calculateMuch') }}</p>
                <div class="form-group">
                  <label class="form-control-label">{{ $t('panel.fees.countryResidence') }}</label>
                  <select class="custom-select" v-model="country">
                    <option v-for="(country, index) in fees" :key="index" :value="index">
                      {{ country.name }}
                    </option>
                  </select>
                </div>
                <div v-if="country !== 'fr'" class="form-group">
                  <label class="form-control-label">{{ $t('panel.fees.estimatedSales') }}</label>
                  <select class="custom-select">
                    <option v-for="(fee, index) in fees[country].paypalWallet" :key="index" :value="index">
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
                    <th>{{ $t('panel.fees.paymentMethod') }}</th>
                    <th>{{ $t('panel.fees.fees') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th>{{ $t('panel.fees.paypal') }}</th>
                    <td>test 1</td>
                  </tr>
                  <tr>
                    <th>{{ $t('panel.fees.cardList') }}</th>
                    <td>test 2</td>
                  </tr>
                  <tr>
                    <th>{{ $t('panel.fees.amex') }}</th>
                    <td>test 3</td>
                  </tr>
                  <tr>
                    <th>{{ $t('panel.fees.lpm') }}</th>
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
        country: 'fr',
      };
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
