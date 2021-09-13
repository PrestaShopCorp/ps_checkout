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
      <b-card no-body footer-class="d-flex justify-content-end">
        <template v-slot:header>
          <i class="material-icons">settings</i>
          {{ $t('pages.resetPassword.resetPassword') }}
        </template>
        <b-card-body>
          <template v-if="emailSent">
            <b-alert variant="success" show>
              <h4 class="alert-heading">
                {{ $t('pages.resetPassword.youGotEmail') }}
              </h4>
              <p>
                {{ $t('pages.resetPassword.sendEmail') }}
              </p>
            </b-alert>
          </template>
          <template v-else>
            <h1 class="text-center mb-4">
              {{ $t('pages.resetPassword.sendLink') }}
            </h1>
          </template>

          <b-alert v-if="errorException.length" variant="danger" show>
            <p>{{ errorException }}</p>
          </b-alert>

          <b-form v-if="!emailSent">
            <b-form-group
              label-cols="4"
              label-align="right"
              :label="$t('pages.resetPassword.email')"
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
          </b-form>
        </b-card-body>

        <template v-slot:footer>
          <div class="d-flex">
            <b-button
              class="mr-3"
              variant="outline-secondary"
              @click="goToSignIn()"
            >
              {{ $t('pages.resetPassword.goBackToLogin') }}
            </b-button>
            <b-button
              v-if="!emailSent"
              id="submit-reset-password-form-button"
              variant="primary"
              @click="resetPassword()"
            >
              {{ $t('pages.resetPassword.reset') }}
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
  import ajax from '@/requests/ajax.js';

  export default {
    name: 'ResetPassword',
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
        emailSent: false
      };
    },
    computed: {
      invalidEmail() {
        if (this.email.state === false) {
          return this.email.errorMessage;
        }
        return '';
      }
    },
    methods: {
      resetPassword() {
        ajax({
          url: this.$store.getters.adminController,
          action: 'SendPasswordResetEmail',
          data: {
            email: this.email.value
          }
        })
          .then(response => {
            if (response.status) {
              this.resetEmailError();
              this.emailSent = true;
            }
          })
          .catch(error => {
            if (error.response) {
              this.handleResponseError(error.response.data);
            } else {
              throw error;
            }
          });
      },
      handleResponseError(response) {
        if (
          undefined !== response.body &&
          undefined !== response.body.error &&
          undefined !== response.body.error.message
        ) {
          this.errorException = '';
          switch (response.body.error.message) {
            case error.INVALID_EMAIL:
              this.setEmailError(false, this.$t('firebase.error.invalidEmail'));
              break;
            case error.MISSING_EMAIL:
              this.setEmailError(false, this.$t('firebase.error.missingEmail'));
              break;
            case error.EMAIL_NOT_FOUND:
              this.setEmailError(
                false,
                this.$t('firebase.error.emailNotFound')
              );
              break;
            default:
              this.setEmailError(false, this.$t('firebase.error.defaultError'));
              break;
          }
        }

        if (
          undefined !== response.exceptionMessage &&
          response.exceptionMessage
        ) {
          this.resetEmailError();
          this.errorException =
            response.exceptionCode + ' > ' + response.exceptionMessage;
        }
      },
      setEmailError(hasError, message) {
        this.email.state = hasError;
        this.email.errorMessage = message;
      },
      resetEmailError() {
        this.email.state = null;
        this.email.errorMessage = '';
      },
      goToSignIn() {
        // eslint-disable-next-line no-console
        this.$router
          .push('/authentication/signin')
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
</style>
