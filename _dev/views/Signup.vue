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
              <input v-model="formFields.email" type="text" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-control-label">Password</label>
              <input v-model="formFields.password" type="password" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-control-label">Confirm password</label>
              <input v-model="formFields.confirmPassword" type="password" class="form-control is-invalid">
              <div class="invalid-feedback">This is a danger label</div>
            </div>
          </div>
        </div>
        <div v-if="currentStep === 2" class="card-block row">
          <div class="card-text max-size">
            <div class="form-group">
              <label class="form-control-label">Full name</label>
              <input v-model="formFields.businessLegalName" type="text" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-control-label">Business legal name</label>
              <input v-model="formFields.businessAddress" type="text" class="form-control">
            </div>
            <div class="row">
              <div class="form-group col">
                <label class="form-control-label">Confirm password</label>
                <input v-model="formFields.businessCountry" type="text" class="form-control">
              </div>
              <div class="form-group col">
                <label class="form-control-label">Confirm password</label>
                <input v-model="formFields.businessPostCode" type="text" class="form-control">
              </div>
            </div>
            <div class="row">
              <div class="form-group col">
                <label class="form-control-label">Confirm password</label>
                <input v-model="formFields.businessCity" type="text" class="form-control">
              </div>
              <div class="form-group col">
                <label class="form-control-label">Confirm password</label>
                <input v-model="formFields.businessIsoCode" type="text" class="form-control">
              </div>
            </div>
            <div class="row">
              <div class="form-group col-3">
                <label class="form-control-label">Tel</label>
                <input v-model="formFields.businessIsoCode" type="text" class="form-control">
              </div>
              <div class="form-group col-6">
                <label class="form-control-label">Confirm password</label>
                <input v-model="formFields.businessPhoneNumber" type="text" class="form-control">
              </div>
            </div>
            <div class="form-group">
              <label class="form-control-label">Full name</label>
              <input v-model="formFields.businessName" type="text" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-control-label">Business legal name</label>
              <input v-model="formFields.businessType" type="text" class="form-control">
            </div>
            <div class="form-group">
              <label class="form-control-label">Business legal name</label>
              <input v-model="formFields.businessSales" type="text" class="form-control">
            </div>
            <div class="form-group">
              <PSCheckbox v-model="formFields.termsOfUse">I have read and agreed with the terms of use of my data.</PSCheckbox>
            </div>
          </div>
        </div>
        <div class="card-footer d-flex">
          <div class="container-fluid pl-0">
            <PSButton ghost @click="previousStep()">Back</PSButton>
          </div>
          <div>
            <PSButton v-if="currentStep === 1" primary @click="nextStep()">Continue</PSButton>
            <PSButton v-if="currentStep === 1" primary @click="signUp()">create</PSButton>
            <PSButton v-if="currentStep === 2" primary @click="createAccount()">Create account</PSButton>
          </div>
        </div>
      </div>
    </form>
    <BlockReassurance />
  </div>
</template>

<script>
import {mapState} from 'vuex';
import PSButton from '@/components/widgets/ps-button';
import PSCheckbox from '@/components/widgets/ps-checkbox';
import BlockReassurance from '@/components/widgets/block-reassurance';
import {request} from '@/requests/ajax.js';

export default {
  components: {
    PSButton,
    PSCheckbox,
    BlockReassurance,
  },
  data() {
    return {
      currentStep: 1,
      formFields: {
        email: '',
        password: '',
        confirmPassword: '',
        businessLegalName: '',
        businessAddress: '',
        businessCountry: '',
        businessPostCode: '',
        businessCity: '',
        businessIsoCode: '',
        businessPhoneNumber: '',
        businessName: '',
        businessType: '',
        businessSales: '',
        termsOfUse: false,
      },
    };
  },
  computed: {
    ...mapState([
      'trans',
    ]),
    confirmPassword() {
      return this.formFields.confirmPassword;
    },
  },
  watch: {
    confirmPassword(val) {
      console.log(val);
    },
  },
  methods: {
    signUp() {
      request({
        action: 'SignUp',
        data: {
          email: this.formFields.email,
          password: this.formFields.password,
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
