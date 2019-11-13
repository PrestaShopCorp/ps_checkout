<!--**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *-->
<template>
  <div>
    <div class="d-flex">
      <form class="form form-horizontal container" @submit.prevent="signUp()">
        <div class="card">
          <div class="card-block row pb-0">
            <div class="card-text header">
              <div class="title mb-3">
                <h1>{{ $t('pages.signup.createYourPsAccount') }}</h1>
              </div>
            </div>
          </div>
          <div class="card-block row pb-0">
            <div class="card-text">
              <div class="form-group row">
                <label class="form-control-label">{{ $t('pages.signup.email') }}</label>
                <div class="col-6">
                  <div class="form-group mb-0" :class="{ 'has-warning' : email.hasError }">
                    <input v-model="email.value" type="text" class="form-control" :class="{ 'is-warning' : email.hasError }">
                    <div v-if="email.hasError" class="warning-feedback">{{ email.errorMessage }}</div>
                  </div>
                </div>
              </div>
              <div class="form-group row">
                <label class="form-control-label">{{ $t('pages.signup.password') }}</label>
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
                  <PSCheckbox id="terms" v-model="terms.value">
                    {{ $t('pages.signup.termsOfUse') }}
                    <a :href="readmeCgu" target="_blank">{{ $t('pages.signup.termsOfUseLinkText') }}</a>
                  </PSCheckbox>
                  <div v-if="terms.hasError" class="warning-feedback">{{ terms.errorMessage }}</div>
                  <div id="privacy" class="mt-4">
                    <p>{{ $t('pages.signup.mentionsTermsText') }} (<a href="mailto:privacy@prestashop.com" target="_blank">{{ $t('pages.signup.mentionsTermsLinkTextPart1') }}</a>)</p>
                    <p><a :href="$t('pages.signup.mentionsTermsLinkPart2')" target="_blank">{{ $t('pages.signup.mentionsTermsLinkTextPart2') }}</a></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer d-flex">
            <div class="container-fluid pl-0">
              <PSButton @click="previous()">{{ $t('pages.signup.back') }}</PSButton>
            </div>
            <div class="d-flex">
              <PSButton class="mr-3" ghost @click="goToSignIn()">{{ $t('pages.signup.signIn') }}</PSButton>
              <PSButton primary type="submit">{{ $t('pages.signup.createAccount') }}</PSButton>
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
          errorMessage: this.$t('pages.signup.termsOfUseError'),
        },
      };
    },
    computed: {
      readmeCgu() {
        return this.$store.state.context.cguUrl;
      },
    },
    methods: {
      signUp() {
        if (!this.terms.value) {
          this.terms.hasError = true;
          return;
        }

        this.terms.hasError = false;

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
          this.setEmailError(true, this.$t('firebase.error.emailExists'));
          this.resetPasswordError();
          break;
        case error.MISSING_PASSWORD:
          this.setPasswordError(true, this.$t('firebase.error.missingPassword'));
          this.resetEmailError();
          break;
        case error.INVALID_EMAIL:
          this.setEmailError(true, this.$t('firebase.error.invalidEmail'));
          this.resetPasswordError();
          break;
        case error.MISSING_EMAIL:
          this.setEmailError(true, this.$t('firebase.error.missingEmail'));
          this.resetPasswordError();
          break;
        default:
          this.setPasswordError(true, this.$t('firebase.error.defaultError'));
          this.setEmailError(true, this.$t('firebase.error.defaultError'));
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
  #privacy {
    font-size: 12px;
    text-align: justify;
  }
</style>
