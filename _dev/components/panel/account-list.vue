<template>
  <form class="form form-horizontal">
    <div class="card">
      <h3 class="card-header">
        <i class="material-icons">settings</i> {{ $t('panel.account-list.accountSettings') }}
      </h3>
      <div class="card-block row">
        <div class="card-text">
          <div class="row mb-2">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12 pl-0">
              <h1 class="text-muted font-weight-light">{{ $t('panel.account-list.activateAllPayment') }}</h1>
            </div>
          </div>
          <div class="row">
            <div class="col-12 col-sm-6 col-md-8 col-lg-8 pl-0">
              <h2>{{ $t('panel.account-list.essentialsAccount') }}</h2>
              <p class="text-muted mb-0">
                <template v-if="firebaseStatusAccount === true">
                  {{ $t('panel.account-list.connectedWitdh') }} <b>{{ $store.state.firebase.account.email }}</b> {{ $t('panel.account-list.account') }}
                </template>
                <template v-else>
                  {{ $t('panel.account-list.createNewAccount') }}
                </template>
              </p>
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-3 m-auto">
              <div class="text-center float-right" v-if="firebaseStatusAccount === false">
                <a href="#" class="btn btn-primary-reverse btn-outline-primary light-button mb-1">
                  {{ $t('panel.account-list.createAccount') }}
                </a>
                <br>
                {{ $t('panel.account-list.or') }}
                <router-link to="/authentication/login">
                  <b>{{ $t('panel.account-list.logIn') }}</b>
                </router-link>
              </div>
              <div class="text-right" v-else>
                <a href="#" class="text-muted" @click.prevent="firebaseLogout()">{{ $t('panel.account-list.logOut') }}</a>
              </div>
            </div>
          </div>
          <div class="row d-block">
            <div class="line-separator my-4" />
          </div>
          <div class="row">
            <div class="col-12 col-sm-6 col-md-8 col-lg-8 pl-0">
              <h2>{{ $t('panel.account-list.paypalAccount') }}</h2>
              <p class="text-muted">
                <template v-if="paypalStatusAccount === false">
                  {{ $t('panel.account-list.activatePayment') }}
                </template>
                <template v-else>
                  {{ $t('panel.account-list.accountIsLinked') }}
                </template>
              </p>
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-3 m-auto">
              <div class="text-center float-right" v-if="paypalStatusAccount === false">
                <a
                  v-show="paypalIsLoaded && paypalOnboardingLink"
                  class="btn btn-primary-reverse btn-outline-primary light-button"
                  data-paypal-button="true"
                  :href="paypalOnboardingLink+'&displayMode=minibrowser'"
                  target="PPFrame"
                >
                  {{ $t('panel.account-list.linkToPaypal') }}
                </a>
                <button
                  v-show="!paypalOnboardingLink"
                  class="btn btn-primary-reverse btn-outline-primary light-button"
                  disabled
                >
                  {{ $t('panel.account-list.linkToPsCheckoutFirst') }}
                </button>
                <a
                  v-show="!paypalIsLoaded"
                  href="#"
                >
                  <b>{{ $t('panel.account-list.loading') }} ...</b>
                </a>
              </div>
              <div class="text-right" v-else>
                <a href="#" class="text-muted" @click.prevent="paypalUnlink()">{{ $t('panel.account-list.useAnotherAccount') }}</a>
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
    computed: {
      firebaseStatusAccount() {
        return this.$store.state.firebase.account.onboardingCompleted;
      },
      paypalStatusAccount() {
        return this.$store.state.paypal.account.onboardingCompleted;
      },
      paypalOnboardingLink() {
        return this.$store.state.paypal.account.paypalOnboardingLink;
      },
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
