<template>
  <form class="form form-horizontal">
    <div class="card">
      <h3 class="card-header">
        <i class="material-icons">picture_in_picture</i> Customize Paypal checkout flow
      </h3>
      <div class="card-block row">
        <div class="card-text">
          <div class="form-group">
            <label class="form-control-label">Paypal checkout</label>
            <select id="form_carrier_options_default_carrier" name="form[carrier_options][default_carrier]" class="custom-select"><option value="-1">Best price</option><option value="-2">Best grade</option><option value="1" selected="selected">PrestaShop</option><option value="2">My carrier</option></select>
            <small class="form-text">Your shop's default carrier</small>
          </div>
        </div>
      </div>
    </div>
  </form>
</template>

<script>
export default {
  components: {
  },
  data() {
    return {
    };
  },
};
</script>
