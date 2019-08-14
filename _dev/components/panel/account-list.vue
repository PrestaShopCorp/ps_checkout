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
                  {{ $t('panel.account-list.connectedWitdh') }} <b>{{ $store.state.firebase.email }}</b> {{ $t('panel.account-list.account') }}
                </template>
                <template v-else>
                  {{ $t('panel.account-list.createNewAccount') }}
                </template>
              </p>
            </div>
            <div v-if="!isReady" class="col-12 col-sm-4 col-md-3 col-lg-3 m-auto">
              <div class="text-center float-right" v-if="firebaseStatusAccount === false">
                <a :href="ssoOnboardingLinkSignin" class="mr-4"><b>{{ $t('panel.account-list.logIn') }}</b></a>
                <a :href="ssoOnboardingLinkCreateAccount" class="btn btn-primary-reverse btn-outline-primary light-button mb-1">
                  {{ $t('panel.account-list.createAccount') }}
                </a>
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
                  {{ $t('panel.account-list.accountIsLinked') }}<br>
                  <b>{{ paypalEmail }}</b>
                </template>
              </p>
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-3 m-auto">
              <div class="text-center float-right" v-if="paypalStatusAccount === false">
                <Onboarding />
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
  import Onboarding from '@/components/block/onboarding';

  export default {
    components: {
      Onboarding,
    },
    computed: {
      isReady() {
        return this.$store.state.context.isReady;
      },
      paypalEmail() {
        return this.$store.state.paypal.emailMerchant;
      },
      firebaseStatusAccount() {
        return this.$store.state.firebase.onboardingCompleted;
      },
      paypalStatusAccount() {
        return this.$store.state.paypal.onboardingCompleted;
      },
      ssoOnboardingLinkSignin() {
        return this.$store.state.firebase.onboardingLinkSignIn;
      },
      ssoOnboardingLinkCreateAccount() {
        return this.$store.state.firebase.onboardingLinkCreateAccount;
      },
    },
    methods: {
      firebaseLogout() {
        this.$store.dispatch('logout');
      },
      paypalUnlink() {
        this.$store.dispatch('unlink').then(() => {
          this.$store.dispatch('getOnboardingLink');
        });
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
