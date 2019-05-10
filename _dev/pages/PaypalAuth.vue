<template>
  <div id="authentication" class="d-flex">
    <div class="card container-fluid clearfix py-4 mr-4">
      <div class="align-item d-flex mx-md-1 mx-lg-5">
        <div class="connect-paypal-content container-fluid">
          <div class="connect-paypal-title">Go live with</div>
          <div class="connect-paypal-prestashop">
            <span class="prestashop">PrestaShop</span>
            <span class="payments"> Payments</span>
          </div>
          <div class="powered-by">
            <span>Powered by <img src="@/assets/images/paypal.png"></span>
          </div>
          <br>
          <div class="connect-paypal-text">
            <p class="firebase-account">
              Your PrestaShop Services account is ready and you are logged in
              with <b>Fabien Lachance</b> account.
            </p>
            <p>
              To activate payment methods link your existing PayPal account
              or create a new one.
            </p>
          </div>
          <div>
            <a class="btn btn-primary" data-paypal-button="true" href="https://www.sandbox.paypal.com/partnerexp/appEntry?referralToken=ZDVhMGE0NmQtM2JjYi00YTFhLTk0NDctZGRjNjVlYzZhMTgxTFN2SklwL1hPRXA1VzVJcDBWNTlXZmVId25vam5SK3JRdW9RVlRUdTVPcz0=&context_token=2080289890742334464&displayMode=minibrowser" target="PPFrame">Link account</a>
          </div>
        </div>
        <div class="connect-paypal-image mr-md-0 mr-lg-5">
          <img src="@/assets/images/almost-ready.png" alt="">
        </div>
      </div>
    </div>
    <Reassurance />
  </div>
</template>

<script>
import {mapState} from 'vuex';
import Reassurance from '@/components/block/reassurance';

export default {
  name: 'PaypalAuth',
  components: {
    Reassurance,
  },
  computed: {
    ...mapState([
      'trans',
    ]),
  },
  created() {
    let script;
    // const ref = document.getElementsByTagName('script')[0];
    const ref = document.getElementsByTagName('script')[0];
    if (!document.getElementById('paypal-js')) {
      script = document.createElement('script');
      script.id = 'paypal-js';
      script.async = true;
      script.src = 'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js';
      ref.parentNode.insertBefore(script, ref);
    }
  },
  methods: {
    paypalOnboarding() {

    },
  },
};
</script>

<style scoped>
.d-flex {
  align-items: flex-start;
}
a.btn.btn-primary {
  text-transform: unset;
}
.align-item {
  align-items: center;
}
.powered-by {
  font-size: 12px;
}
.prestashop {
  color: #15082E;
}
.payments {
  color: #D01665;
}
.connect-paypal-content {
  text-align: left;
}
.connect-paypal-title {
  color: #363A41;
  font-size: 32px;
  line-height: 42px;
  padding-bottom: 10px;
}
.connect-paypal-prestashop {
  color: #363A41;
  font-size: 40px;
  font-weight: 600;
  line-height: 42px;
  padding-bottom: 10px;
}
.connect-paypal-text {
  color: #363A41;
  font-size: 16px;
  line-height: 24px;
  max-width: 500px;
  padding-top: 10px;
  padding-bottom: 10px;
}
.firebase-account {
  color: #6C868E;
  font-size: 14px;
}
</style>
