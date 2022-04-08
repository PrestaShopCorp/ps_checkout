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
  <b-card no-body>
    <template v-slot:header>
      <i class="material-icons">picture_in_picture</i>
      {{ $t('panel.pay-in-4x.title') }}
    </template>

    <b-card-body>
      <b-col sm="12" md="10" lg="10" class="m-auto">
        <b-form>
          <b-form-group
            id="fieldset-1"
            :label="$t('panel.express-checkout.pageLocation')"
            label-for="express-checkout"
          >
            <b-form-row class="mr-0 ml-0 text-center">
              <b-col>
                <PSCheckbox
                  id="order-page"
                  v-model="orderPageIsActive"
                  class="mb-2"
                  :centered="true"
                />
                <img
                  @click="toggleOrderPage()"
                  v-if="orderPageIsActive"
                  class="active-img mb-2"
                  src="@/assets/images/preview_cart-page_active.png"
                  alt=""
                />
                <img
                  @click="toggleOrderPage()"
                  v-else
                  class="mb-2"
                  src="@/assets/images/preview_cart-page_inactive.png"
                  alt=""
                />
                <div>{{ $t('panel.express-checkout.orderPage') }}</div>
                <div class="text-muted">
                  ({{ $t('panel.express-checkout.recommended') }})
                </div>
              </b-col>
              <b-col>
                <PSCheckbox
                  id="product-page"
                  v-model="productPageIsActive"
                  class="mb-2"
                  :centered="true"
                />
                <img
                  @click="toggleProductPage()"
                  v-if="productPageIsActive"
                  class="active-img mb-2"
                  src="@/assets/images/preview_product-page_active.png"
                  alt=""
                />
                <img
                  @click="toggleProductPage()"
                  v-else
                  class="mb-2"
                  src="@/assets/images/preview_product-page_inactive.png"
                  alt=""
                />
                <div>{{ $t('panel.express-checkout.productPage') }}</div>
                <div class="text-muted">
                  ({{ $t('panel.express-checkout.recommended') }})
                </div>
              </b-col>
            </b-form-row>
          </b-form-group>

          <!-- Legal text provided by PayPal displayed only in FR -->
          <div class="text-muted small">
            Affichez le Paiement en 4X PayPal sur votre site.
            <br />
            <br />
            Le Paiement en 4X PayPal permet aux consommateurs français de payer
            en 4 versements égaux. Vous pouvez promouvoir le Paiement en 4X
            PayPal uniquement si vous êtes un commerçant basé en France, avec un
            site internet en français et une intégration PayPal standard. Les
            marchands ayant l’outil Vaulting (coffre-fort numérique) ou une
            intégration de paiements récurrents/abonnement, ainsi que ceux
            présentant certaines activités (vente de biens numériques / de biens
            non physiques) ne sont pas éligibles pour promouvoir le Paiement en
            4X PayPal.
            <br />
            <br />
            Nous afficherons des messages sur votre site pour promouvoir le
            Paiement en 4X PayPal. Vous ne pouvez pas promouvoir le Paiement en
            4X PayPal avec un autre contenu, quel qu’il soit.
            <a
              href="https://www.paypal.com/fr/business/buy-now-pay-later"
              target="_blank"
            >
              En savoir plus
            </a>
          </div>
        </b-form>
      </b-col>
    </b-card-body>
    <b-card-body>
      <b-col sm="12" md="10" lg="10" class="m-auto">
        <b-alert variant="info" show>
          <h4 class="alert-heading">
            {{ $t('panel.express-checkout.alertTitle') }}
          </h4>
          <p>
            {{ $t('panel.pay-in-4x.alert-content') }}
          </p>
        </b-alert>
        <b-alert variant="warning" show>
          <p>
            {{ $t('pages.customize.pay4xEligibilityWarning') }}
            <a
              href="https://developer.paypal.com/docs/checkout/pay-later/us/"
              target="_blank"
              class="link-underline"
            >
              https://developer.paypal.com/docs/checkout/pay-later/us/
            </a>.
          </p>
        </b-alert>
      </b-col>
    </b-card-body>
  </b-card>
</template>

<script>
  // TODO: Add translations to template
  import PSCheckbox from '@/components/form/checkbox';

  export default {
    components: {
      PSCheckbox
    },
    computed: {
      orderPageIsActive: {
        get() {
          return this.$store.state.configuration.payIn4X.orderPage;
        },
        set(payload) {
          this.$store.dispatch('togglePayIn4XOrderPage', payload);
        }
      },
      productPageIsActive: {
        get() {
          return this.$store.state.configuration.payIn4X.productPage;
        },
        set(payload) {
          this.$store.dispatch('togglePayIn4XProductPage', payload);
        }
      }
    },
    methods: {
      toggleOrderPage() {
        this.$store.dispatch('togglePayIn4XOrderPage', !this.orderPageIsActive);
      },
      toggleProductPage() {
        this.$store.dispatch(
          'togglePayIn4XProductPage',
          !this.productPageIsActive
        );
      }
    }
  };
</script>

<style scoped>
  img {
    cursor: pointer;
    border-style: solid !important;
    outline: 1px solid #cfcfcf;
  }
  .active-img {
    outline: 2px solid #25b9d7;
  }
</style>
