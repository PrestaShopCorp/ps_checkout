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
  <form class="form form-horizontal">
    <div class="card">
      <h3 class="card-header">
        <b>{{ $t('panel.accounts.checkout.title') }}</b>
      </h3>

      <div class="card-body">
        <div class="d-flex align-items-center">
          <img
            src="@/assets/images/logo.png"
            width="50"
            alt="Checkout"
            class="ml-1"
          />

          <p class="text-muted fs-14 mb-0 ml-3 mr-auto">
            <template v-if="checkoutAccountStatus">
              {{ $t('panel.accounts.checkout.connectedWith') }}

              <b>{{ checkoutEmail }}</b>

              {{ $t('panel.accounts.checkout.account') }}
            </template>

            <template v-else>
              {{ $t('panel.accounts.checkout.createNewAccount') }}
            </template>
          </p>

          <AccountStatusCheckout v-if="checkoutAccountStatus" class="mr-3" />

          <div class="text-center float-right" v-if="!checkoutAccountStatus">
            <a
              id="go-to-signin-link"
              href="#"
              @click.prevent="goToSignIn()"
              class="mr-4"
            >
              <b>{{ $t('panel.accounts.checkout.logIn') }}</b>
            </a>

            <a
              id="go-to-signup-link"
              href="#"
              @click.prevent="goToSignUp()"
              class="btn btn-primary-reverse btn-outline-primary light-button mb-1"
            >
              {{ $t('panel.accounts.checkout.createAccount') }}
            </a>
          </div>

          <div class="text-right" v-else>
            <b-button
              v-if="!isReady"
              id="psx-logout-button"
              href="#"
              data-toggle="modal"
              data-target="#modalLogout"
              variant="outline-secondary"
            >
              {{ $t('panel.accounts.checkout.logOut') }}
            </b-button>
          </div>

          <!-- modal -->
          <div
            class="modal"
            id="modalLogout"
            tabindex="-1"
            role="dialog"
            aria-labelledby="psxModalLogout"
          >
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="psxModalLogout">
                    {{ $t('panel.accounts.checkout.titleLogout') }}
                  </h5>

                  <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close"
                  >
                    <span aria-hidden="true">×</span>
                  </button>
                </div>

                <div class="modal-body">
                  <p>{{ $t('panel.accounts.checkout.descriptionLogout') }}</p>
                </div>

                <div class="modal-footer">
                  <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-dismiss="modal"
                  >
                    {{ $t('panel.accounts.checkout.cancel') }}
                  </button>

                  <button
                    @click.prevent="logOut()"
                    id="modal-confirm-logout-button"
                    type="button"
                    class="btn btn-primary"
                    data-dismiss="modal"
                  >
                    {{ $t('panel.accounts.checkout.logOut') }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</template>

<script>
  import AccountStatusCheckout from '@/components/block/account-status-checkout.vue';

  export default {
    components: {
      AccountStatusCheckout
    },
    props: ['sendTrack'],
    computed: {
      isReady() {
        return this.$store.state.context.isReady;
      },
      checkoutEmail() {
        return this.$store.state.firebase.email;
      },
      checkoutAccountStatus() {
        return this.$store.state.firebase.onboardingCompleted;
      }
    },
    methods: {
      goToSignIn() {
        this.$router
          .push('/authentication/signin')
          // eslint-disable-next-line no-console
          .catch(exception => console.log(exception));
      },
      goToSignUp() {
        this.$router
          .push('/authentication/signup')
          // eslint-disable-next-line no-console
          .catch(exception => console.log(exception));
      },
      logOut() {
        this.$store.dispatch('logOut').then(() => {
          this.$store.dispatch('unlink');
          this.$store.dispatch('psxOnboarding', false);
          this.sendTrack();
        });
      }
    }
  };
</script>

<style scoped>
  .nobootstrap .table {
    border: unset;
    border-radius: unset;
  }
  .nobootstrap .table tr:first-child td {
    border-top: 0 !important;
  }

  .nobootstrap .table,
  .nobootstrap .table tr:last-child td {
    border-bottom: 0 !important;
  }

  .line-separator {
    height: 1px;
    opacity: 0.2;
    background: #6b868f;
    border-bottom: 2px solid #6b868f;
  }
  #app .modal {
    background: rgba(0, 0, 0, 0.4);
  }
  #app .modal-content {
    border-radius: unset;
  }
  #app .modal-body {
    font-size: 14px;
  }
  #app #modalLogout .modal-dialog {
    top: 35%;
  }
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.2s;
  }
  .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
    opacity: 0;
  }
  .fs-14 {
    font-size: 14px;
  }
</style>
