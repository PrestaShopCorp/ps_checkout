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
      {{ $t('pages.customize.payLater.title') }}
    </template>
    <b-card-body>
      <b-col sm="12" md="10" lg="10" class="m-auto">
        <b-alert variant="warning" show>
          <p>
            {{ $t('pages.customize.payLater.eligibilityWarning') }}
            <a :href="eligibilityLink" target="_blank" class="link-underline">
              {{ $t('pages.customize.payLater.eligibilityLink') }}
            </a>
            .
          </p>
        </b-alert>
      </b-col>
    </b-card-body>
    <b-card-body>
      <b-col sm="12" md="10" lg="10" class="m-auto">
        <b-form>
          <b-form-group
            id="fieldset-1"
            :label="$t('pages.customize.payLater.message.title')"
            label-for="pay-later-message"
          >
            <b-form-row class="mr-0 ml-0 text-center">
              <b-col>
                <PSCheckbox
                  id="order-page"
                  v-model="orderPageMessageIsActive"
                  class="mb-2"
                  :centered="true"
                />
                <img
                  @click="toggleOrderPageMessage()"
                  v-if="orderPageMessageIsActive"
                  class="active-img mb-2"
                  src="@/assets/images/preview_cart-page_active.png"
                  alt=""
                />
                <img
                  @click="toggleOrderPageMessage()"
                  v-else
                  class="mb-2"
                  src="@/assets/images/preview_cart-page_inactive.png"
                  alt=""
                />
                <div>{{ $t('panel.express-checkout.orderPage') }}</div>
              </b-col>
              <b-col>
                <PSCheckbox
                  id="product-page"
                  v-model="productPageMessageIsActive"
                  class="mb-2"
                  :centered="true"
                />
                <img
                  @click="toggleProductPageMessage()"
                  v-if="productPageMessageIsActive"
                  class="active-img mb-2"
                  src="@/assets/images/preview_product-page_active.png"
                  alt=""
                />
                <img
                  @click="toggleProductPageMessage()"
                  v-else
                  class="mb-2"
                  src="@/assets/images/preview_product-page_inactive.png"
                  alt=""
                />
                <div>{{ $t('panel.express-checkout.productPage') }}</div>
              </b-col>
            </b-form-row>
          </b-form-group>
        </b-form>
      </b-col>
    </b-card-body>
    <b-card-body>
      <b-col sm="12" md="10" lg="10" class="m-auto">
        <b-form>
          <b-form-group
            id="fieldset-1"
            :label="$t('pages.customize.payLater.banner.title')"
            label-for="pay-later-banner"
          >
            <b-form-row class="mr-0 ml-0 text-center">
              <b-col>
                <PSCheckbox
                  id="home-page"
                  v-model="homePageBannerIsActive"
                  class="mb-2"
                  :centered="true"
                />
                <img
                  @click="toggleHomePageBanner()"
                  v-if="homePageBannerIsActive"
                  class="active-img mb-2"
                  src="@/assets/images/preview_cart-page_active.png"
                  alt=""
                />
                <img
                  @click="toggleHomePageBanner()"
                  v-else
                  class="mb-2"
                  src="@/assets/images/preview_cart-page_inactive.png"
                  alt=""
                />
                <div>{{ $t('pages.customize.payLater.homePage') }}</div>
              </b-col>
              <b-col>
                <PSCheckbox
                  id="category-page"
                  v-model="categoryPageBannerIsActive"
                  class="mb-2"
                  :centered="true"
                />
                <img
                  @click="toggleCategoryPageBanner()"
                  v-if="categoryPageBannerIsActive"
                  class="active-img mb-2"
                  src="@/assets/images/preview_cart-page_active.png"
                  alt=""
                />
                <img
                  @click="toggleCategoryPageBanner()"
                  v-else
                  class="mb-2"
                  src="@/assets/images/preview_cart-page_inactive.png"
                  alt=""
                />
                <div>{{ $t('pages.customize.payLater.categoryPage') }}</div>
              </b-col>
              <b-col>
                <PSCheckbox
                  id="order-page"
                  v-model="orderPageBannerIsActive"
                  class="mb-2"
                  :centered="true"
                />
                <img
                  @click="toggleOrderPageBanner()"
                  v-if="orderPageBannerIsActive"
                  class="active-img mb-2"
                  src="@/assets/images/preview_cart-page_active.png"
                  alt=""
                />
                <img
                  @click="toggleOrderPageBanner()"
                  v-else
                  class="mb-2"
                  src="@/assets/images/preview_cart-page_inactive.png"
                  alt=""
                />
                <div>{{ $t('panel.express-checkout.orderPage') }}</div>
              </b-col>
              <b-col>
                <PSCheckbox
                  id="product-page"
                  v-model="productPageBannerIsActive"
                  class="mb-2"
                  :centered="true"
                />
                <img
                  @click="toggleProductPageBanner()"
                  v-if="productPageBannerIsActive"
                  class="active-img mb-2"
                  src="@/assets/images/preview_product-page_active.png"
                  alt=""
                />
                <img
                  @click="toggleProductPageBanner()"
                  v-else
                  class="mb-2"
                  src="@/assets/images/preview_product-page_inactive.png"
                  alt=""
                />
                <div>{{ $t('panel.express-checkout.productPage') }}</div>
              </b-col>
            </b-form-row>
          </b-form-group>
        </b-form>
      </b-col>
    </b-card-body>
    <b-card-body>
      <b-col sm="12" md="10" lg="10" class="m-auto">
        <b-form>
          <b-form-group
            id="fieldset-1"
            :label="$t('pages.customize.payLater.button.title')"
            label-for="paylater-buttons"
          >
            <b-form-row class="mr-0 ml-0 text-center">
              <b-col>
                <PSCheckbox
                  id="cart-page"
                  v-model="cartPageButtonIsActive"
                  class="mb-2"
                  :centered="true"
                />
                <img
                  @click="toggleCartPageButton()"
                  v-if="cartPageButtonIsActive"
                  class="active-img mb-2"
                  src="@/assets/images/preview_cart-page_active.png"
                  alt=""
                />
                <img
                  @click="toggleCartPageButton()"
                  v-else
                  class="mb-2"
                  src="@/assets/images/preview_cart-page_inactive.png"
                  alt=""
                />
                <div>{{ $t('panel.express-checkout.orderPage') }}</div>
              </b-col>
              <b-col>
                <PSCheckbox
                  id="order-page"
                  v-model="orderPageButtonIsActive"
                  class="mb-2"
                  :centered="true"
                />
                <img
                  @click="toggleOrderPageButton()"
                  v-if="orderPageButtonIsActive"
                  class="active-img mb-2"
                  src="@/assets/images/preview_checkout-page_active.png"
                  alt=""
                />
                <img
                  @click="toggleOrderPageButton()"
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
                  v-model="productPageButtonIsActive"
                  class="mb-2"
                  :centered="true"
                />
                <img
                  @click="toggleProductPageButton()"
                  v-if="productPageButtonIsActive"
                  class="active-img mb-2"
                  src="@/assets/images/preview_product-page_active.png"
                  alt=""
                />
                <img
                  @click="toggleProductPageButton()"
                  v-else
                  class="mb-2"
                  src="@/assets/images/preview_product-page_inactive.png"
                  alt=""
                />
                <div>{{ $t('panel.express-checkout.productPage') }}</div>
              </b-col>
            </b-form-row>
          </b-form-group>
        </b-form>
      </b-col>
    </b-card-body>
    <b-card-body>
      <b-col sm="12" md="10" lg="10" class="m-auto">
        <b-alert variant="info" show>
          <p>
            {{ $t('pages.customize.payLater.adviceContent') }}
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
      orderPageMessageIsActive: {
        get() {
          return this.$store.state.configuration.payLater.orderPage.message;
        },
        set(payload) {
          this.$store.dispatch('togglePayLaterOrderPageMessage', payload);
        }
      },
      productPageMessageIsActive: {
        get() {
          return this.$store.state.configuration.payLater.productPage.message;
        },
        set(payload) {
          this.$store.dispatch('togglePayLaterProductPageMessage', payload);
        }
      },
      orderPageBannerIsActive: {
        get() {
          return this.$store.state.configuration.payLater.orderPage.banner;
        },
        set(payload) {
          this.$store.dispatch('togglePayLaterOrderPageBanner', payload);
        }
      },
      productPageBannerIsActive: {
        get() {
          return this.$store.state.configuration.payLater.productPage.banner;
        },
        set(payload) {
          this.$store.dispatch('togglePayLaterProductPageBanner', payload);
        }
      },
      categoryPageBannerIsActive: {
        get() {
          return this.$store.state.configuration.payLater.categoryPage.banner;
        },
        set(payload) {
          this.$store.dispatch('togglePayLaterCategoryPageBanner', payload);
        }
      },
      homePageBannerIsActive: {
        get() {
          return this.$store.state.configuration.payLater.homePage.banner;
        },
        set(payload) {
          this.$store.dispatch('togglePayLaterHomePageBanner', payload);
        }
      },
      cartPageButtonIsActive: {
        get() {
          return this.$store.state.configuration.payLater.cartPage.button;
        },
        set(payload) {
          this.$store.dispatch('togglePayLaterCartPageButton', payload);
        }
      },
      orderPageButtonIsActive: {
        get() {
          return this.$store.state.configuration.payLater.orderPage.button;
        },
        set(payload) {
          this.$store.dispatch('togglePayLaterOrderPageButton', payload);
        }
      },
      productPageButtonIsActive: {
        get() {
          return this.$store.state.configuration.payLater.productPage.button;
        },
        set(payload) {
          this.$store.dispatch('togglePayLaterProductPageButton', payload);
        }
      },
      eligibilityLink() {
        switch (this.$store.state.paypal.countryMerchant) {
          case 'FR':
            return 'https://developer.paypal.com/docs/checkout/pay-later/fr/';
          case 'GB':
            return 'https://developer.paypal.com/docs/checkout/pay-later/gb/';
          case 'ES':
            return 'https://developer.paypal.com/docs/checkout/pay-later/es/';
          case 'IT':
            return 'https://developer.paypal.com/docs/checkout/pay-later/it/';
          case 'US':
            return 'https://developer.paypal.com/docs/checkout/pay-later/us/';
          case 'DE':
            return 'https://developer.paypal.com/docs/checkout/pay-later/de/';
          case 'AU':
            return 'https://developer.paypal.com/docs/checkout/pay-later/au/';
          default:
            return 'https://developer.paypal.com/docs/checkout/pay-later/gb/';
        }
      }
    },
    methods: {
      toggleOrderPageMessage() {
        this.$store.dispatch(
          'togglePayLaterOrderPageMessage',
          !this.orderPageMessageIsActive
        );
      },
      toggleProductPageMessage() {
        this.$store.dispatch(
          'togglePayLaterProductPageMessage',
          !this.productPageMessageIsActive
        );
      },
      toggleOrderPageBanner() {
        this.$store.dispatch(
          'togglePayLaterOrderPageBanner',
          !this.orderPageBannerIsActive
        );
      },
      toggleProductPageBanner() {
        this.$store.dispatch(
          'togglePayLaterProductPageBanner',
          !this.productPageBannerIsActive
        );
      },
      toggleHomePageBanner() {
        this.$store.dispatch(
          'togglePayLaterHomePageBanner',
          !this.homePageBannerIsActive
        );
      },
      toggleCategoryPageBanner() {
        this.$store.dispatch(
          'togglePayLaterCategoryPageBanner',
          !this.categoryPageBannerIsActive
        );
      },
      toggleCartPageButton() {
        this.$store.dispatch(
          'togglePayLaterCartPageButton',
          !this.cartPageButtonIsActive
        );
      },
      toggleOrderPageButton() {
        this.$store.dispatch(
          'togglePayLaterOrderPageButton',
          !this.orderPageButtonIsActive
        );
      },
      toggleProductPageButton() {
        this.$store.dispatch(
          'togglePayLaterProductPageButton',
          !this.productPageButtonIsActive
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
