<template>
  <div>
    <div class="d-flex">
      <form class="form form-horizontal container" @submit.prevent="signUp()">
        <div class="card">
          <div class="card-block row pb-0">
            <div class="card-text header">
              <div class="title mb-3">
                <h1>Create your PrestaShop Checkout account</h1>
              </div>
            </div>
          </div>
          <div class="card-block row pb-0">
            <div class="card-text">
              <div class="form-group row">
                <label class="form-control-label">Email</label>
                <div class="col-6">
                  <div class="form-group mb-0" :class="{ 'has-warning' : email.hasError }">
                    <input v-model="email.value" type="text" class="form-control" :class="{ 'is-warning' : email.hasError }">
                    <div v-if="email.hasError" class="warning-feedback">{{ email.errorMessage }}</div>
                  </div>
                </div>
              </div>
              <div class="form-group row">
                <label class="form-control-label">Password</label>
                <div class="col-6">
                  <div class="form-group mb-0" :class="{ 'has-warning' : password.hasError }">
                    <input v-model="password.value" type="password" class="form-control" :class="{ 'is-warning' : password.hasError }">
                    <div v-if="password.hasError" class="warning-feedback">{{ password.errorMessage }}</div>
                  </div>
                </div>
              </div>
              <div class="form-group row">
                <label class="form-control-label">&nbsp;</label>
                <div class="col-6">
                  <PSCheckbox id="terms" v-model="terms.value">I accept the terms of use</PSCheckbox>
                  <div v-if="terms.hasError" class="warning-feedback">{{ terms.errorMessage }}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer d-flex">
            <div class="container-fluid pl-0">
              <PSButton @click="previous()">Back</PSButton>
            </div>
            <div class="d-flex">
              <PSButton class="mr-3" ghost @click="goToSignIn()">Sign in</PSButton>
              <PSButton primary type="submit">Create account</PSButton>
            </div>
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
  import PSCheckbox from '@/components/form/checkbox';

  export default {
    name: 'Signup',
    components: {
      PSButton,
      Reassurance,
      PSCheckbox,
    },
    data() {
      return {
        email: {
          value: '',
          hasError: false,
          errorMessage: '',
        },
        password: {
          value: '',
          hasError: false,
          errorMessage: '',
        },
        terms: {
          value: false,
          hasError: false,
          errorMessage: 'You need to accept our terms and conditions to continue',
        },
      };
    },
    methods: {
      signUp() {
        if (this.terms.value) {
          this.terms.hasError = false;
        } else {
          this.terms.hasError = true;
          return;
        }

        this.$store.dispatch({
          type: 'signUp',
          email: this.email.value,
          password: this.password.value,
        }).then(() => {
          this.$router.push('/authentication');
        }).catch((err) => {
          this.handleResponseError(err.error.message);
        });
      },
      goToSignIn() {
        this.$router.push('/authentication/signin');
      },
      handleResponseError(err) {
        switch (err) {
        case error.EMAIL_EXISTS:
          this.setEmailError(true, 'Email already exist.');
          this.resetPasswordError();
          break;
        case error.MISSING_PASSWORD:
          this.setPasswordError(true, 'The password is missing.');
          this.resetEmailError();
          break;
        case error.INVALID_EMAIL:
          this.setEmailError(true, 'The email address is badly formatted.');
          this.resetPasswordError();
          break;
        case error.MISSING_EMAIL:
          this.setEmailError(true, 'The email is missing.');
          this.resetPasswordError();
          break;
        default:
          this.setPasswordError(true, 'There is an error.');
          this.setEmailError(true, 'There is an error.');
          break;
        }
      },
      setPasswordError(hasError, message) {
        this.password.hasError = hasError;
        this.password.errorMessage = message;
      },
      setEmailError(hasError, message) {
        this.email.hasError = hasError;
        this.email.errorMessage = message;
      },
      resetEmailError() {
        this.email.hasError = false;
        this.email.errorMessage = '';
      },
      resetPasswordError() {
        this.password.hasError = false;
        this.password.errorMessage = '';
      },
      previous() {
        this.$router.push('/authentication');
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