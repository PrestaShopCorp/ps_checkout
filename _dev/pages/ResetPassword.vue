<template>
  <div>
    <div class="d-flex">
      <form class="form form-horizontal container" @submit.prevent="resetPassword()">
        <div class="card">
          <h3 class="card-header">
            <i class="material-icons">settings</i> {{ $t('pages.resetPassword.resetPassword') }}
          </h3>
          <template v-if="emailSent">
            <div class="card-block row pb-0">
              <div class="card-text header text-left my-4">
                <h1>{{ $t('pages.resetPassword.youGotEmail') }}</h1>
                <h2>{{ $t('pages.resetPassword.sendEmail') }}</h2>
              </div>
            </div>
          </template>
          <template v-else>
            <div class="card-block row pb-0">
              <div class="card-text header">
                <div class="title mb-3">
                  <h1>{{ $t('pages.resetPassword.sendLink') }}</h1>
                </div>
              </div>
            </div>
            <div class="card-block row pb-0">
              <div class="card-text">
                <div class="form-group row">
                  <label class="form-control-label">{{ $t('pages.resetPassword.email') }}</label>
                  <div class="col-6">
                    <div class="form-group mb-0" :class="{ 'has-warning' : email.hasError }">
                      <input v-model="email.value" type="text" class="form-control" :class="{ 'is-warning' : email.hasError }">
                      <div v-if="email.hasError" class="warning-feedback">{{ email.errorMessage }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>
          <div class="card-footer d-flex justify-content-end">
            <PSButton class="mr-3" ghost @click="goToSignIn()">{{ $t('pages.resetPassword.goBackToLogin') }}</PSButton>
            <PSButton v-if="!emailSent" primary type="submit">{{ $t('pages.resetPassword.reset') }}</PSButton>
          </div>
        </div>
      </form>
    </div>
    <div class="row">
      <div class="container">
        <Reassurance />
      </div>
    </div>
  </div>
</template>

<script>
  import PSButton from '@/components/form/button';
  import * as error from '@/lib/auth';
  import Reassurance from '@/components/block/reassurance';
  import ajax from '@/requests/ajax.js';

  export default {
    name: 'ResetPassword',
    components: {
      PSButton,
      Reassurance,
    },
    data() {
      return {
        email: {
          value: '',
          hasError: false,
          errorMessage: '',
        },
        emailSent: false,
      };
    },
    methods: {
      resetPassword() {
        ajax({
          url: this.$store.getters.adminController,
          action: 'SendPasswordResetEmail',
          data: {
            email: this.email.value,
          },
        }).then((response) => {
          if (response.error) {
            this.handleResponseError(response.error.message);
            return;
          }
          this.resetEmailError();
          this.emailSent = true;
        });
      },
      handleResponseError(err) {
        switch (err) {
        case error.INVALID_EMAIL:
          this.setEmailError(true, this.$t('firebase.error.invalidEmail'));
          break;
        case error.MISSING_EMAIL:
          this.setEmailError(true, this.$t('firebase.error.missingEmail'));
          break;
        case error.EMAIL_NOT_FOUND:
          this.setEmailError(true, this.$t('firebase.error.emailNotFound'));
          break;
        default:
          this.setEmailError(true, this.$t('firebase.error.defaultError'));
          break;
        }
      },
      setEmailError(hasError, message) {
        this.email.hasError = hasError;
        this.email.errorMessage = message;
      },
      resetEmailError() {
        this.email.hasError = false;
        this.email.errorMessage = '';
      },
      goToSignIn() {
        this.$router.push('/authentication/signin');
      },
    },
  };
</script>

<style scoped>
  .card-text.header {
    text-align: center;
  }
  .card-text .title {
    font-size: 32px;
  }
  .card-text .text {
    font-size: 16px;
  }
  .card-text .step {
    font-size: 16px;
    font-weight: 600;
  }
  .d-flex {
    align-items: flex-start;
  }
  .max-size {
    max-width: 480px !important;
  }
</style>
