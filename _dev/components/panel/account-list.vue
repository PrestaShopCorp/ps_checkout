<template>
  <form class="form form-horizontal" v-bind:class="[modalLogout ? 'modal-open' : '']">
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
            <div class="col-12 col-sm-4 col-md-3 col-lg-4 m-auto">
              <div class="text-center float-right" v-if="firebaseStatusAccount === false">
                <a href="#" @click.prevent="goToSignIn()" class="mr-4"><b>{{ $t('panel.account-list.logIn') }}</b></a>
                <a href="#" @click.prevent="goToSignUp()" class="btn btn-primary-reverse btn-outline-primary light-button mb-1">
                  {{ $t('panel.account-list.createAccount') }}
                </a>
              </div>
              <div class="text-right" v-else>
                <a v-if="!isReady" href="#" @click.prevent="showModal()" class="text-muted">{{ $t('panel.account-list.logOut') }}</a>
              </div>
              <!-- modal -->
              <transition name="fade">
                <div v-if="modalLogout" class="modal show" id="modalLogout" tabindex="-1" role="dialog" aria-labelledby="psxModalLogout" style="padding-right: 15px; display: block;">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="psxModalLogout">{{ $t('panel.account-list.titleLogout') }}</h5>
                              <button @click.prevent="hideModal()" type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">×</span>
                              </button>
                            </div>
                            <div class="modal-body">
                                <p>{{ $t('panel.account-list.descriptionLogout') }}</p>
                            </div>
                            <div class="modal-footer">
                              <button @click.prevent="hideModal()" type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ $t('panel.account-list.close') }}</button>
                              <button @click.prevent="logOut()" type="button" class="btn btn-primary">{{ $t('panel.account-list.logOut') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
              </transition>
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
            <div class="col-12 col-sm-4 col-md-3 col-lg-4 m-auto">
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
    data() {
      return {
        modalLogout: false,
      };
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
    },
    methods: {
      goToSignIn() {
        this.$router.push('/authentication/signin');
      },
      goToSignUp() {
        this.$router.push('/authentication/signup');
      },
      showModal() {
        this.modalLogout = true;
      },
      hideModal() {
        this.modalLogout = false;
      },
      logOut() {
        this.$store.dispatch('logOut').then(() => {
          this.$store.dispatch('unlink');
          this.$store.dispatch('psxOnboarding', false);
          this.hideModal();
        });
      },
      paypalUnlink() {
        this.$store.dispatch('unlink').then(() => {
          this.$store.dispatch('getOnboardingLink');
        });
      },
    },
    created() {
      if (this.firebaseStatusAccount) {
        this.$store.dispatch('getOnboardingLink');
      }
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
#app .modal{
  background: rgba(0,0,0,0.4);
}
#app #modalLogout .modal-dialog{
  top: 35%;
}
.fade-enter-active, .fade-leave-active {
 transition: opacity .2s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
 opacity: 0;
}
</style>
