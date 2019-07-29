<template>
  <div>
    <template v-if="firebaseStatusAccount">
      <a
        v-show="paypalIsLoaded && paypalOnboardingLink"
        class="btn btn-primary-reverse btn-outline-primary light-button"
        data-paypal-button="true"
        :href="paypalOnboardingLink+'&displayMode=minibrowser'"
        target="PPFrame"
      >
        {{ $t('panel.account-list.linkToPaypal') }}
      </a>
      <a
        v-show="!paypalIsLoaded"
        href="#"
      >
        <b>{{ $t('panel.account-list.loading') }} ...</b>
      </a>
    </template>
    <template v-else>
      <button
        class="btn btn-primary-reverse btn-outline-primary light-button"
        disabled
      >
        {{ $t('panel.account-list.linkToPsCheckoutFirst') }}
      </button>
    </template>
  </div>
</template>

<script>
  export default {
    name: 'Onboarding',
    data() {
      return {
        paypalIsLoaded: false,
      };
    },
    computed: {
      paypalOnboardingLink() {
        return this.$store.state.paypal.account.paypalOnboardingLink;
      },
      firebaseStatusAccount() {
        return this.$store.state.firebase.account.onboardingCompleted;
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
