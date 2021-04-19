<!--**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *-->

<template>
  <div>
    <b-container>
      <b-card no-body footer-class="d-flex" id="signup-form-card">
        <b-card-body>
          <h1 class="text-center mb-4">
            {{ $t('pages.signup.createYourPsAccount') }}
          </h1>

          <b-alert v-if="errorException.length" variant="danger" show>
            <p>{{ errorException }}</p>
          </b-alert>

          <b-form>
            <b-form-group
              label-cols="4"
              label-align="right"
              :label="$t('pages.signup.email')"
              label-for="email-input"
              :invalid-feedback="invalidEmail"
              :class="{ 'has-danger': email.state === false }"
            >
              <b-form-input
                id="email-input"
                v-model="email.value"
                :state="email.state"
              />
            </b-form-group>

            <b-form-group
              label-cols="4"
              label-align="right"
              :label="$t('pages.signup.password')"
              label-for="password-input"
              :invalid-feedback="invalidPassword"
              :class="{ 'has-danger': password.state === false }"
            >
              <b-form-input
                id="password-input"
                v-model="password.value"
                type="password"
                :state="password.state"
              />
            </b-form-group>

            <b-form-group
              label-cols="4"
              label-align="right"
              label-for="terms"
              :invalid-feedback="invalidTerms"
              :state="terms.state"
            >
              <PSCheckbox id="terms" v-model="terms.value">
                {{ $t('pages.signup.termsOfUse') }}
                <b-link :href="readmeCgu" target="_blank">
                  {{ $t('pages.signup.termsOfUseLinkText') }}
                </b-link>
              </PSCheckbox>
            </b-form-group>

            <b-form-group
              label-cols="4"
              label-align="right"
              label-for="privacy"
            >
              <div id="privacy" class="mt-4">
                <p>
                  {{ $t('pages.signup.mentionsTermsText') }}
                  (
                  <b-link href="mailto:privacy@prestashop.com" target="_blank">
                    {{ $t('pages.signup.mentionsTermsLinkTextPart1') }}
                  </b-link>
                  )
                </p>
                <p>
                  <b-link
                    :href="$t('pages.signup.mentionsTermsLinkPart2')"
                    target="_blank"
                  >
                    {{ $t('pages.signup.mentionsTermsLinkTextPart2') }}
                  </b-link>
                </p>
              </div>
            </b-form-group>
          </b-form>
        </b-card-body>

        <template v-slot:footer>
          <div class="container-fluid pl-0">
            <b-button variant="secondary" @click="previous()">
              {{ $t('pages.signup.back') }}
            </b-button>
          </div>
          <div class="d-flex">
            <b-button
              id="go-to-signin-button"
              class="mr-3"
              variant="outline-secondary"
              @click="goToSignIn()"
            >
              {{ $t('pages.signup.signIn') }}
            </b-button>
            <b-button id="signup-button" variant="primary" @click="signUp()">
              {{ $t('pages.signup.createAccount') }}
            </b-button>
          </div>
        </template>
      </b-card>
    </b-container>

    <b-container class="mt-4">
      <Reassurance />
    </b-container>
  </div>
</template>

<script>
  import * as error from '@/lib/auth';
  import Reassurance from '@/components/block/reassurance';
  import PSCheckbox from '@/components/form/checkbox';

  export default {
    name: 'Signup',
    components: {
      Reassurance,
      PSCheckbox
    },
    data() {
      return {
        errorException: '',
        email: {
          value: '',
          state: null,
          errorMessage: ''
        },
        password: {
          value: '',
          state: null,
          errorMessage: ''
        },
        terms: {
          value: false,
          state: null,
          errorMessage: this.$t('pages.signup.termsOfUseError')
        }
      };
    },
    computed: {
      readmeCgu() {
        return this.$store.state.context.cguUrl;
      },
      invalidEmail() {
        if (this.email.state === false) {
          return this.email.errorMessage;
        }
        return '';
      },
      invalidPassword() {
        if (this.password.state === false) {
          return this.password.errorMessage;
        }
        return '';
      },
      invalidTerms() {
        if (this.terms.state === false) {
          return this.terms.errorMessage;
        }
        return '';
      }
    },
    methods: {
      signUp() {
        if (!this.terms.value) {
          this.terms.state = false;
          return;
        }

        this.terms.state = null;

        this.$store
          .dispatch({
            type: 'signUp',
            email: this.email.value,
            password: this.password.value
          })
          .then(response => {
            this.$store
              .dispatch({
                type: 'openOnboardingSession',
                sessionData: {
                  account_id: response.body.localId,
                  account_email: response.body.email
                }
              })
              .then(() => {
                this.$router
                  .push('/authentication')
                  // eslint-disable-next-line no-console
                  .catch(exception => console.log(exception));
              });
          })
          .catch(response => {
            this.handleResponseError(response);
          });
      },
      goToSignIn() {
        this.$router
          .push('/authentication/signin')
          // eslint-disable-next-line no-console
          .catch(exception => console.log(exception));
      },
      handleResponseError(response) {
        if (
          undefined !== response.body &&
          undefined !== response.body.error &&
          undefined !== response.body.error.message
        ) {
          switch (response.body.error.message) {
            case error.EMAIL_EXISTS:
              this.setEmailError(false, this.$t('firebase.error.emailExists'));
              this.resetPasswordError();
              break;
            case error.MISSING_PASSWORD:
              this.setPasswordError(
                false,
                this.$t('firebase.error.missingPassword')
              );
              this.resetEmailError();
              break;
            case error.INVALID_EMAIL:
              this.setEmailError(false, this.$t('firebase.error.invalidEmail'));
              this.resetPasswordError();
              break;
            case error.MISSING_EMAIL:
              this.setEmailError(false, this.$t('firebase.error.missingEmail'));
              this.resetPasswordError();
              break;
            default:
              this.setPasswordError(
                false,
                this.$t('firebase.error.defaultError')
              );
              this.setEmailError(false, this.$t('firebase.error.defaultError'));
              break;
          }
        }

        if (undefined !== response.body) {
          this.errorException = response.body;
        }
      },
      setPasswordError(hasError, message) {
        this.password.state = hasError;
        this.password.errorMessage = message;
      },
      setEmailError(hasError, message) {
        this.email.state = hasError;
        this.email.errorMessage = message;
      },
      resetEmailError() {
        this.email.state = null;
        this.email.errorMessage = '';
      },
      resetPasswordError() {
        this.password.state = null;
        this.password.errorMessage = '';
      },
      previous() {
        this.$router
          .push('/authentication')
          // eslint-disable-next-line no-console
          .catch(exception => console.log(exception));
      }
    }
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
