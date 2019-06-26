<template>
  <form class="form form-horizontal">
    <div class="card">
      <h3 class="card-header">
        <i class="material-icons">settings</i> Account settings
      </h3>
      <div class="card-block row">
        <div class="card-text">
          <div class="row">
            <div class="col-12 col-sm-6 col-md-8 col-lg-8 pl-0">
              <h2>PrestaShop Essentials account</h2>
              <p class="text-muted mb-0">
                <template v-if="$store.state.firebase.account.status === false">
                  Activate all payment methods with your PrestaShop Essentials account.
                </template>
                <template v-else>
                  You are connected with
                </template>
              </p>
              <p class="text-muted mb-0">
                <template v-if="$store.state.firebase.account.status === false">
                  Create a new account or login with your current account.
                </template>
                <template v-else>
                  <b>{{ $store.state.firebase.account.email }}</b>
                </template>
              </p>
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-3 m-auto">
              <div class="text-center float-right" v-if="$store.state.firebase.account.status === false">
                <a href="#" class="btn btn-primary-reverse btn-outline-primary light-button mb-1">
                  Create account
                </a>
                <br>
                or
                <a href="#">
                  <b>Log in</b>
                </a>
              </div>
              <div class="text-right" v-else>
                <a href="#" class="text-muted" @click.prevent="firebaseLogout()">Log out</a>
              </div>
            </div>
          </div>
          <div class="row d-block">
            <div class="line-separator my-4" />
          </div>
          <div class="row">
            <div class="col-12 col-sm-6 col-md-8 col-lg-8 pl-0">
              <h2>PayPal account</h2>
              <p class="text-muted">
                <template v-if="$store.state.paypal.account.status === false">
                  To activate payment methods link your existing PayPal account
                  or create a new one.
                </template>
                <template v-else>
                  Your PrestaShop Essentials account is linked with this PayPal account.
                </template>
              </p>
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-3 m-auto">
              <div class="text-center float-right" v-if="$store.state.paypal.account.status === false">
                <a
                  v-show="paypalIsLoaded"
                  class="btn btn-primary-reverse btn-outline-primary light-button"
                  data-paypal-button="true"
                  :href="$store.state.paypal.account.paypalOnboardingLink+'&displayMode=minibrowser'"
                  target="PPFrame"
                >
                  Link to PayPal account
                </a>
                <a
                  v-show="!paypalIsLoaded"
                  href="#"
                >
                  <b>Loading ...</b>
                </a>
              </div>
              <div class="text-right" v-else>
                <a href="#" class="text-muted" @click.prevent="paypalUnlink()">Use another account</a>
              </div>
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
        paypalIsLoaded: false,
      };
    },
    components: {
    },
    methods: {
      firebaseLogout() {
        this.$store.dispatch('logout');
      },
      paypalUnlink() {
        this.$store.dispatch('unlink');
      },
    },
    destroyed() {
      const paypalScript = document.getElementById('paypal-js');
      const signupScript = document.getElementById('signup-js');
      const bizScript = document.getElementById('biz-js');

      paypalScript.parentNode.removeChild(paypalScript);
      signupScript.parentNode.removeChild(signupScript);
      bizScript.parentNode.removeChild(bizScript);
    },
    created() {
      const paypalScript = document.createElement('script');
      paypalScript.setAttribute('src', 'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js');
      paypalScript.setAttribute('id', 'paypal-js');
      paypalScript.setAttribute('async', 'true');
      document.head.appendChild(paypalScript);
    },
    mounted() {
      const interval = setInterval(() => {
        if (window.PAYPAL !== undefined
          && Object.keys(window.PAYPAL.apps).length > 0
          && Object.keys(window.PAYPAL.apps.Signup).length > 0
          && Object.keys(window.PAYPAL.apps.Signup.MiniBrowser).length > 1
        ) {
          window.PAYPAL.apps.Signup.MiniBrowser.init();
          this.paypalIsLoaded = true;
          clearInterval(interval);
        }
      }, 200);
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
