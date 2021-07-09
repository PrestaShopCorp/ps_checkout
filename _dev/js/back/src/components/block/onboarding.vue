<!--**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *-->
<template>
  <div>
    <template v-if="checkoutAccountStatus">
      <button
        id="link-to-paypal-account-button"
        v-show="paypalOnboardingLink"
        @click.prevent="paypalOnboarding()"
        class="btn btn-primary-reverse btn-outline-primary light-button"
      >
        {{ $t('panel.accounts.paypal.linkToPaypal') }}
      </button>
      <a
        class="btn btn-primary-reverse btn-outline-primary light-button d-none"
        data-paypal-button="true"
        :href="paypalOnboardingLink + '&displayMode=minibrowser'"
        target="PPFrame"
        ref="paypalButton"
      >
        {{ $t('panel.accounts.paypal.linkToPaypal') }}
      </a>
      <a v-show="!paypalOnboardingLink" href="#">
        <b>{{ $t('panel.accounts.paypal.loading') }} ...</b>
      </a>
    </template>
    <template v-else>
      <button
        class="btn btn-primary-reverse btn-outline-primary light-button"
        disabled
      >
        {{ $t('panel.accounts.paypal.linkToPsCheckoutFirst') }}
      </button>
    </template>
  </div>
</template>

<script>
  export default {
    name: 'Onboarding',
    data() {
      return {
        paypalIsLoaded: false
      };
    },
    computed: {
      paypalOnboardingLink() {
        let onboardingSession = this.$store.state.session.onboarding;

        if (!onboardingSession) {
          return null;
        }

        let dataShop = onboardingSession.data.shop;

        return dataShop && dataShop.paypal_onboarding_url
          ? dataShop.paypal_onboarding_url
          : null;
      },
      checkoutAccountStatus() {
        return this.$store.state.firebase.onboardingCompleted;
      }
    },
    methods: {
      paypalOnboarding() {
        if (window && window.analytics) {
          this.$segment.track('Paypal Lightbox triggered', {
            category: 'ps_checkout'
          });
        }

        if (
          this.paypalOnboardingLink !== false &&
          this.paypalOnboardingLink.length > 0
        ) {
          this.$refs.paypalButton.click();
          return;
        }

        // TODO put this part in a component
        let time = 0;
        const poll = setInterval(() => {
          if (!window.analytics) {
            return;
          }
          time++;
          // the callback is fired when window.analytics is available and before any other hit is sent
          this.$segment.track(`Event for more than ${time} minutes`, {
            category: 'ps_checkout'
          });

          if (time >= 4) {
            clearInterval(poll);
          }
        }, 60000);
      }
    },
    destroyed() {
      const paypalScript = document.getElementById('paypal-js');
      const signupScript = document.getElementById('signup-js');
      const bizScript = document.getElementById('biz-js');

      if (paypalScript !== null) {
        paypalScript.parentNode.removeChild(paypalScript);
      }

      if (signupScript !== null) {
        signupScript.parentNode.removeChild(signupScript);
      }

      if (bizScript !== null) {
        bizScript.parentNode.removeChild(bizScript);
      }
    },
    created() {
      const paypalScript = document.createElement('script');
      paypalScript.setAttribute(
        'src',
        'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js'
      );
      paypalScript.setAttribute('id', 'paypal-js');
      paypalScript.setAttribute('async', 'true');
      document.head.appendChild(paypalScript);

      setInterval(() => {
        let onboardingSession = this.$store.state.session.onboarding;

        if (
          onboardingSession &&
          onboardingSession.data &&
          onboardingSession.data.form &&
          (!onboardingSession.data.shop ||
            !onboardingSession.data.shop.paypal_onboarding_url)
        ) {
          this.$store.dispatch('getOpenedOnboardingSession');
        }
      }, 10000);
    },
    mounted() {
      const interval = setInterval(() => {
        if (
          window.PAYPAL !== undefined &&
          Object.keys(window.PAYPAL.apps).length > 0 &&
          Object.keys(window.PAYPAL.apps.Signup).length > 0 &&
          Object.keys(window.PAYPAL.apps.Signup.MiniBrowser).length > 1
        ) {
          window.PAYPAL.apps.Signup.MiniBrowser.init();
          this.paypalIsLoaded = true;
          clearInterval(interval);
        }
      }, 200);
    }
  };
</script>
