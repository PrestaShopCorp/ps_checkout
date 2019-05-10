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
              <PSAlert alert-type="ALERT_TYPE_DANGER" :has-close="true" @closeAlert="hasError = false">
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
    <Reassurance />
  </div>
</template>

<script>
import {mapState} from 'vuex';
import PSButton from '@/components/form/button';
import PSAlert from '@/components/form/alert';
import Reassurance from '@/components/block/reassurance';
import {request} from '@/requests/ajax.js';

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
    ...mapState([
      'trans',
    ]),
  },
  methods: {
    logIn() {
      request({
        action: 'SignIn',
        data: {
          email: this.email,
          password: this.password,
        },
      }).then((user) => {
        if (user.error) {
          this.hasError = true;

          switch (user.error.message) {
          case 'EMAIL_NOT_FOUND':
            this.errorMessage = 'There is no user record corresponding to this identifier. The user may have been deleted.';
            break;
          case 'INVALID_EMAIL':
            this.errorMessage = 'The email address is badly formatted.';
            break;
          case 'INVALID_PASSWORD':
            this.errorMessage = 'The password is invalid.';
            break;
          default:
            this.errorMessage = 'There is an error.';
            break;
          }
        } else {
          this.$store.dispatch('updateFirebaseAccount', {
            firebase: {
              email: user.email,
              idToken: user.idToken,
              localId: user.localId,
              refreshToken: user.refreshToken,
            },
          }).then(() => {
            this.$router.push('/authentication/paypal');
          });
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
