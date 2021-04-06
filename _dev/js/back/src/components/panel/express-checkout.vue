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
      {{ $t('panel.express-checkout.title') }}
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
              <b-col v-if="shopIs17">
                <PSCheckbox
                  id="checkout-page"
                  v-model="checkoutPageIsActive"
                  class="mb-2"
                  :centered="true"
                />
                <img
                  @click="toggleCheckoutPage()"
                  v-if="checkoutPageIsActive"
                  class="active-img mb-2"
                  src="@/assets/images/preview_checkout-page_active.png"
                  alt=""
                />
                <img
                  @click="toggleCheckoutPage()"
                  v-else
                  class="mb-2"
                  src="@/assets/images/preview_checkout-page_inactive.png"
                  alt=""
                />
                <div>{{ $t('panel.express-checkout.checkoutPage') }}</div>
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
              </b-col>
            </b-form-row>
          </b-form-group>

          <div class="text-muted small">
            {{ $t('panel.express-checkout.shippingCost') }}
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
            {{ $t('panel.express-checkout.alertContent') }}
          </p>
        </b-alert>
      </b-col>
    </b-card-body>
  </b-card>
</template>

<script>
  import PSCheckbox from '@/components/form/checkbox';

  export default {
    components: {
      PSCheckbox
    },
    computed: {
      orderPageIsActive: {
        get() {
          return this.$store.state.configuration.expressCheckout.orderPage;
        },
        set(payload) {
          this.$store.dispatch('toggleECOrderPage', payload);
          this.$segment.track(
            payload ? 'CKT Enable Express cart' : 'CKT Disable Express cart',
            {
              category: 'ps_checkout'
            }
          );
        }
      },
      checkoutPageIsActive: {
        get() {
          return this.$store.state.configuration.expressCheckout.checkoutPage;
        },
        set(payload) {
          this.$store.dispatch('toggleECCheckoutPage', payload);
          this.$segment.track(
            payload ? 'CKT Enable Express Login' : 'CKT Disable Express Login',
            {
              category: 'ps_checkout'
            }
          );
        }
      },
      productPageIsActive: {
        get() {
          return this.$store.state.configuration.expressCheckout.productPage;
        },
        set(payload) {
          this.$store.dispatch('toggleECProductPage', payload);
          this.$segment.track(
            payload
              ? 'CKT Enable Express product'
              : 'CKT Disable Express product',
            {
              category: 'ps_checkout'
            }
          );
        }
      },
      shopIs17() {
        return this.$store.getters.shopIs17;
      }
    },
    methods: {
      toggleOrderPage() {
        let status = !this.orderPageIsActive;
        this.$store.dispatch('toggleECOrderPage', status);
        this.$segment.track(
          status ? 'CKT Enable Express cart' : 'CKT Disable Express cart',
          {
            category: 'ps_checkout'
          }
        );
      },
      toggleCheckoutPage() {
        let status = !this.checkoutPageIsActive;
        this.$store.dispatch('toggleECCheckoutPage', status);
        this.$segment.track(
          status ? 'CKT Enable Express Login' : 'CKT Disable Express Login',
          {
            category: 'ps_checkout'
          }
        );
      },
      toggleProductPage() {
        let status = !this.productPageIsActive;
        this.$store.dispatch('toggleECProductPage', status);
        this.$segment.track(
          status ? 'CKT Enable Express product' : 'CKT Disable Express product',
          {
            category: 'ps_checkout'
          }
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
