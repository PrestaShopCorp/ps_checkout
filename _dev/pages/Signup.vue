<template>
  <div class="d-flex">
    <form class="form form-horizontal container">
      <div class="card">
        <div class="card-block row pb-0">
          <div class="card-text header">
            <div class="title mb-3">
              <h3>Create your PrestaShop Services account</h3>
            </div>
            <div class="text">
              <p class="mb-0">Please enter your information below to proceed to the next step.</p>
              <p>So we can build your account and connect to PayPal.</p>
            </div>
          </div>
        </div>
        <div class="card-block row">
          <div class="card-text max-size">
            <div class="form-group">
              <label class="form-control-label">Email</label>
              <input v-model="email" type="text" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-control-label">Password</label>
              <input v-model="password" type="password" class="form-control">
            </div>
            <!-- <div class="form-group">
              <label class="form-control-label">Confirm password</label>
              <input v-model="form.confirmPassword" type="password" class="form-control is-invalid">
              <div class="invalid-feedback">This is a danger label</div>
            </div> -->
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
          <PSButton primary @click="signUp()">
            Create account
          </PSButton>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
  import PSButton from '@/components/form/button';
  import PSCheckbox from '@/components/form/checkbox';
  import PSAlert from '@/components/form/alert';
  import {ALERT_TYPE_DANGER} from '@/lib/alert';
  import {EMAIL_EXISTS} from '@/lib/auth';

  export default {
    components: {
      PSButton,
      PSAlert,
      PSCheckbox,
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
      signUp() {
        this.hasError = false;
        this.$store.dispatch({
          type: 'signUp',
          email: this.email,
          password: this.password,
        }).then(() => {
          this.$router.push('/authentication');
        }).catch((err) => {
          this.hasError = true;
          switch (err.error.message) {
          case EMAIL_EXISTS:
            this.errorMessage = 'Email already exist.';
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
