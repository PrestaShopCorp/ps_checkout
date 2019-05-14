<template>
  <div class="d-flex">
    <form class="form form-horizontal container-fluid">
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
            <div class="step">
              <span>Step {{ currentStep }} of 2</span>
            </div>
          </div>
        </div>
        <div v-if="currentStep === 1" class="card-block row">
          <div class="card-text max-size">
            <div class="form-group">
              <label class="form-control-label">Email</label>
              <input v-model="form.email" type="text" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-control-label">Password</label>
              <input v-model="form.password" type="password" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-control-label">Confirm password</label>
              <input v-model="form.confirmPassword" type="password" class="form-control is-invalid">
              <div class="invalid-feedback">This is a danger label</div>
            </div>
          </div>
        </div>
        <div v-if="currentStep === 2" class="card-block row">
          <div class="card-text max-size">
            <div class="form-group">
              <label class="form-control-label">Full name</label>
              <input v-model="form.business.businessLegalName" type="text" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-control-label">Business legal name</label>
              <input v-model="form.business.address" type="text" class="form-control">
            </div>
            <div class="row">
              <div class="form-group col">
                <label class="form-control-label">Confirm password</label>
                <input v-model="form.business.country" type="text" class="form-control">
              </div>
              <div class="form-group col">
                <label class="form-control-label">Confirm password</label>
                <input v-model="form.business.postCode" type="text" class="form-control">
              </div>
            </div>
            <div class="row">
              <div class="form-group col">
                <label class="form-control-label">Confirm password</label>
                <input v-model="form.business.city" type="text" class="form-control">
              </div>
              <div class="form-group col">
                <label class="form-control-label">Confirm password</label>
                <input v-model="form.business.isoCode" type="text" class="form-control">
              </div>
            </div>
            <div class="row">
              <div class="form-group col-3">
                <label class="form-control-label">Tel</label>
                <input v-model="form.business.isoCode" type="text" class="form-control">
              </div>
              <div class="form-group col-6">
                <label class="form-control-label">Confirm password</label>
                <input v-model="form.business.phoneNumber" type="text" class="form-control">
              </div>
            </div>
            <div class="form-group">
              <label class="form-control-label">Full name</label>
              <input v-model="form.business.name" type="text" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-control-label">Business legal name</label>
              <input v-model="form.business.type" type="text" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-control-label">Business legal name</label>
              <input v-model="form.business.sales" type="text" class="form-control">
            </div>
            <div class="form-group">
              <PSCheckbox v-model="form.termsOfUse">I have read and agreed with the terms of use of my data.</PSCheckbox>
            </div>
          </div>
        </div>
        <div class="card-footer d-flex">
          <div class="container-fluid pl-0">
            <PSButton ghost @click="previousStep()">Back</PSButton>
          </div>
          <div>
            <template v-if="currentStep === 1">
              <PSButton primary @click="nextStep()">Continue</PSButton>
              <PSButton primary @click="signUp()">create</PSButton>
            </template>
            <PSButton v-else-if="currentStep === 2" primary @click="createAccount()">Create account</PSButton>
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
import PSCheckbox from '@/components/form/checkbox';
import Reassurance from '@/components/block/reassurance';
import {EMAIL_NOT_FOUND, INVALID_EMAIL, INVALID_PASSWORD} from '@/lib/auth';

export default {
  components: {
    PSButton,
    PSCheckbox,
    Reassurance,
  },
  data() {
    return {
      currentStep: 1,
      form: {
        email: '',
        password: '',
        confirmPassword: '',
        termsOfUse: false,
        business: {
          legalName: '',
          address: '',
          country: '',
          postCode: '',
          city: '',
          isoCode: '',
          phoneNumber: '',
          name: '',
          type: '',
          sales: '',
        },
      },
      hasError: false,
      errorMessage: '',
    };
  },
  computed: {
    ...mapState([
      'trans',
    ]),
    confirmPassword() {
      return this.form.confirmPassword;
    },
  },
  watch: {
    confirmPassword(val) {
      console.log(val);
    },
  },
  methods: {
    signUp() {
      this.$store.dispatch({
        type: 'signup',
        email: this.form.email,
        password: this.form.password,
      }).then((payload) => {
        this.currentStep = 2;
      }).catch((err) => {
        console.log(err);
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
    checkPasswordMatch() {

    },
    nextStep() {
      this.currentStep = 2;
    },
    previousStep() {
      if (this.currentStep === 1) {
        this.$router.push('/authentication');
      }
      this.currentStep = 1;
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
