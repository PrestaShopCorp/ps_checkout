<template>
  <div class="d-flex">
    <form class="form form-horizontal container-fluid">
      <div class="card">
        <div class="card-block row pb-0">
          <div class="card-text header">
            <div class="title mb-3">
              <h3>Connect to your PrestaShop Services account</h3>
            </div>
            <div class="text">
              <p class="mb-0">Please enter your information below to proceed to the next step.</p>
              <p>So we can build your account and connect to PayPal.</p>
            </div>
          </div>
        </div>
        <div class="card-block row pb-0">
          <div class="card-text max-size">
            <div class="form-group">
              <label class="form-control-label">Email</label>
              <input v-model="email" type="text" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-control-label">Password</label>
              <input v-model="password" type="password" class="form-control">
            </div>
          </div>
        </div>
        <div v-if="hasError" class="card-block row py-0">
          <div class="card-text max-size">
            <div class="form-group">
              <PSAlert
                :alert-type="ALERT_TYPE_DANGER"
                :has-close="true"
                @closeAlert="hasError = false"
              >
                {{ errorMessage }}
              </PSAlert>
            </div>
          </div>
        </div>
        <div class="card-footer d-flex">
          <div class="container-fluid pl-0">
            <PSButton ghost @click="previous()">Back</PSButton>
          </div>
          <div>
            <PSButton primary @click="logIn()">Log in</PSButton>
          </div>
        </div>
      </div>
    </form>
    <Reassurance url="https://google.com" />
  </div>
</template>

<script>
  import PSButton from '@/components/form/button';
  import PSAlert from '@/components/form/alert';
  import Reassurance from '@/components/block/reassurance';
  import {EMAIL_NOT_FOUND, INVALID_EMAIL, INVALID_PASSWORD} from '@/lib/auth';
  import {ALERT_TYPE_DANGER} from '@/lib/alert';

  export default {
    name: 'Login',
    components: {
      PSButton,
      PSAlert,
      Reassurance,
    },
    data() {
      return {
        email: '',
        password: '',
        hasError: false,
        errorMessage: '',
      };
    },
    computed: {
      ALERT_TYPE_DANGER: () => ALERT_TYPE_DANGER,
    },
    methods: {
      logIn() {
        this.$store.dispatch({
          type: 'login',
          email: this.email,
          password: this.password,
        }).then(() => {
          this.$router.push('/authentication/paypal');
        }).catch((err) => {
          this.hasError = true;
          switch (err.error.message) {
          case EMAIL_NOT_FOUND:
            this.errorMessage = 'There is no user record corresponding to this identifier. The user may have been deleted.';
            break;
          case INVALID_EMAIL:
            this.errorMessage = 'The email address is badly formatted.';
            break;
          case INVALID_PASSWORD:
            this.errorMessage = 'The password is invalid.';
            break;
          default:
            this.errorMessage = 'There is an error.';
            break;
          }
        });
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
