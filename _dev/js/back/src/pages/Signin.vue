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
      <b-card no-body footer-class="d-flex" id="login-form-card">
        <b-card-body>
          <h1 class="text-center mb-4">
            {{ $t('pages.signin.logInWithYourPsAccount') }}
          </h1>

          <b-alert v-if="errorException.length" variant="danger" show>
            <p>{{ errorException }}</p>
          </b-alert>

          <b-form>
            <b-form-group
              label-cols="4"
              label-align="right"
              :label="$t('pages.signin.email')"
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
              :label="$t('pages.signin.password')"
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
              label-for="reset-password"
            >
              <b-button
                id="go-to-reset-password-button"
                variant="link"
                @click="goToResetPassword()"
                class="px-0"
              >
                {{ $t('pages.signin.forgotPassword') }}
              </b-button>
            </b-form-group>
          </b-form>
        </b-card-body>

        <template v-slot:footer>
          <div class="container-fluid pl-0">
            <b-button
              id="back-to-previous-page-button"
              variant="secondary"
              @click="previous()"
            >
              {{ $t('pages.signin.back') }}
            </b-button>
          </div>
          <div class="d-flex">
            <b-button id="login-button" variant="primary" @click="logIn()">
              {{ $t('pages.signin.login') }}
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

  export default {
    name: 'Signin',
    components: {
      Reassurance
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
        }
      };
    },
    computed: {
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
      }
    },
    methods: {
      logIn() {
        this.$store
          .dispatch({
            type: 'signIn',
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
          .catch(error => {
            if (error.response) {
              this.handleResponseError(error.response.data);
            } else {
              throw error;
            }
          });
      },
      goToResetPassword() {
        this.$router
          .push('/authentication/reset')
          // eslint-disable-next-line no-console
          .catch(exception => console.log(exception));
      },
      handleResponseError(response) {
        if (
          undefined !== response.body &&
          undefined !== response.body.error &&
          undefined !== response.body.error.message
        ) {
          this.errorException = '';
          switch (response.body.error.message) {
            case error.EMAIL_NOT_FOUND:
              this.setEmailError(
                false,
                this.$t('firebase.error.emailNotFound')
              );
              this.resetPasswordError();
              break;
            case error.INVALID_EMAIL:
              this.setEmailError(false, this.$t('firebase.error.invalidEmail'));
              this.resetPasswordError();
              break;
            case error.INVALID_PASSWORD:
              this.setPasswordError(
                false,
                this.$t('firebase.error.invalidPassword')
              );
              this.resetEmailError();
              break;
            case error.MISSING_PASSWORD:
              this.setPasswordError(
                false,
                this.$t('firebase.error.missingPassword')
              );
              this.resetEmailError();
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
        if (
          undefined !== response.exceptionMessage &&
          response.exceptionMessage
        ) {
          this.resetEmailError();
          this.resetPasswordError();
          this.errorException =
            response.exceptionCode + ' > ' + response.exceptionMessage;
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
