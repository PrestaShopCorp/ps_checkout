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
  <b-container v-if="!closed">
    <div class="value-proposition-card">
      <b-row no-gutters class="text-center">
        <b-col xl="3" class="left d-none d-xl-block">
          <div class="wrapper w-100 h-100">
            <div class="wrapper-content">
              <img
                src="@/assets/images/pscheckout.png"
                class="img-fluid w-100"
                alt=""
              />
            </div>
          </div>
        </b-col>
        <b-col xs="12" xl="9" class="right">
          <div class="wrapper w-100 h-100">
            <b-container class="wrapper-content">
              <b-row class="titles d-none d-md-flex">
                <b-col xs="12" md="3">
                  <div class="title">
                    {{ $t('banner.paypalValueProposition.titleFees') }}
                  </div>
                </b-col>
                <b-col xs="12" md="6">
                  <div class="title w-50">
                    {{
                      $t('banner.paypalValueProposition.titlePaymentMethods')
                    }}
                  </div>
                </b-col>
                <b-col xs="12" md="3">
                  <div class="title">
                    {{ $t('banner.paypalValueProposition.titleConversions') }}
                  </div>
                </b-col>
              </b-row>
              <b-row class="leafs">
                <b-col xs="12" md="3" class="d-block d-md-none">
                  <div class="title">
                    {{ $t('banner.paypalValueProposition.titleFees') }}
                  </div>
                </b-col>
                <b-col xs="12" md="3" class="leaf-wrapper">
                  <div class="h-100 w-100 leaf wrapper">
                    <div class="wrapper-content">
                      <p class="fees-title">
                        {{ $t('banner.paypalValueProposition.fees.title') }}
                      </p>
                      <p class="fees-value">
                        <span>1,2%</span>
                        + 0,35€
                      </p>
                      <p class="fees-subtitle">
                        {{ $t('banner.paypalValueProposition.fees.subtitle') }}
                        <a
                          :href="
                            $t('banner.paypalValueProposition.fees.linkHref')
                          "
                        >
                          {{
                            $t('banner.paypalValueProposition.fees.linkLabel')
                          }}
                        </a>
                      </p>
                    </div>
                    <div id="info-trigger" class="info-trigger">
                      i
                    </div>
                    <b-popover
                      target="info-trigger"
                      triggers="hover"
                      custom-class="value-proposition-card-popup"
                      placement="righttop"
                    >
                      <div class="left">
                        <img
                          src="@/assets/images/lamp.svg"
                          class="img-fluid value-proposition-card-popup-image"
                          alt=""
                        />
                      </div>
                      <div class="right">
                        {{ $t('banner.paypalValueProposition.fees.popup') }}
                        <br />
                        <br />
                        <a
                          :href="
                            $t('banner.paypalValueProposition.fees.linkHref')
                          "
                        >
                          {{
                            $t(
                              'banner.paypalValueProposition.fees.popupLinkLabel'
                            )
                          }}
                        </a>
                      </div>
                    </b-popover>
                  </div>
                </b-col>
                <b-col xs="12" md="6" class="d-block d-md-none">
                  <div class="title">
                    {{
                      $t('banner.paypalValueProposition.titlePaymentMethods')
                    }}
                  </div>
                </b-col>
                <b-col xs="12" md="6" class="leaf-wrapper">
                  <div class="h-100 w-100 leaf logos wrapper">
                    <div class="wrapper-content">
                      <b-row no-gutters align-v="center" align-h="center">
                        <b-col cols="3" sm="2" md="3" lg="2">
                          <img
                            src="@/assets/images/funding-sources/visa.svg"
                            class="img-fluid w-100"
                            alt=""
                          />
                        </b-col>
                        <b-col cols="3" sm="2" md="3" lg="2">
                          <img
                            src="@/assets/images/funding-sources/amex.svg"
                            class="img-fluid w-100"
                            alt=""
                          />
                        </b-col>
                        <b-col cols="3" sm="2" md="3" lg="2">
                          <img
                            src="@/assets/images/funding-sources/mastercard.svg"
                            class="img-fluid w-100"
                            alt=""
                          />
                        </b-col>
                        <b-col cols="3" sm="2" md="3" lg="2">
                          <img
                            src="@/assets/images/funding-sources/paypal.svg"
                            class="img-fluid w-100 img-paypal"
                            alt=""
                          />
                        </b-col>
                        <b-col cols="3" sm="2" md="3" lg="2">
                          <img
                            src="@/assets/images/funding-sources/ideal.svg"
                            class="img-fluid w-100 img-ideal"
                            alt=""
                          />
                        </b-col>
                        <b-col cols="3" sm="2" md="3" lg="2">
                          <img
                            src="@/assets/images/funding-sources/sofort.svg"
                            class="img-fluid w-100"
                            alt=""
                          />
                        </b-col>
                        <b-col cols="3" sm="2" md="3" lg="2">
                          <img
                            src="@/assets/images/funding-sources/bancontact.svg"
                            class="img-fluid w-100"
                            alt=""
                          />
                        </b-col>
                        <b-col cols="3" sm="2" md="3" lg="2">
                          <img
                            src="@/assets/images/funding-sources/p24.svg"
                            class="img-fluid w-100"
                            alt=""
                          />
                        </b-col>
                        <b-col cols="3" sm="2" md="3" lg="2">
                          <img
                            src="@/assets/images/funding-sources/mybank.svg"
                            class="img-fluid w-100"
                            alt=""
                          />
                        </b-col>
                        <b-col cols="3" sm="2" md="3" lg="2">
                          <img
                            src="@/assets/images/funding-sources/eps.svg"
                            class="img-fluid w-100"
                            alt=""
                          />
                        </b-col>
                        <b-col cols="3" sm="2" md="3" lg="2">
                          <img
                            src="@/assets/images/funding-sources/giropay.svg"
                            class="img-fluid w-100"
                            alt=""
                          />
                        </b-col>
                      </b-row>
                    </div>
                  </div>
                </b-col>
                <b-col xs="12" md="3" class="d-block d-md-none">
                  <div class="title">
                    {{ $t('banner.paypalValueProposition.titleConversions') }}
                  </div>
                </b-col>
                <b-col xs="12" md="3" class="leaf-wrapper">
                  <div class="h-100 w-100 leaf wrapper">
                    <div class="wrapper-content">
                      <p class="fees-title">
                        {{
                          $t('banner.paypalValueProposition.conversions.title')
                        }}
                      </p>
                      <img
                        src="@/assets/images/shopping-cart.svg"
                        class="img-fluid conversions-image"
                        alt=""
                      />
                    </div>
                  </div>
                </b-col>
              </b-row>
            </b-container>
          </div>
        </b-col>
      </b-row>
      <div class="close-button" v-if="closeable" @click="onClose">&#10006;</div>
    </div>
  </b-container>
</template>

<script>
  export default {
    name: 'PaypalValueProposition',
    data: () => ({
      closed: false
    }),
    props: {
      closeable: {
        type: Boolean,
        default: false
      }
    },
    watch: {
      closeable(newValue) {
        if (!newValue) {
          this.closed = false;
        }
      }
    },
    methods: {
      onClose() {
        this.closed = true;
        this.$emit('onClose');
      }
    }
  };
</script>

<style scoped>
  @media (max-width: 767px) {
    .value-proposition-card .right {
      padding: 2rem 2rem 0 2rem !important;
    }
  }

  .value-proposition-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 0 30px -20px rgba(0, 0, 0, 0.75);

    margin-bottom: 1rem;
    overflow: hidden;

    position: relative;
  }

  .close-button {
    cursor: pointer;
    user-select: none;

    color: #ffffff;
    font-size: 0.75rem;
    line-height: 1.05rem;

    position: absolute;
    top: 0.7rem;
    right: 0.7rem;

    padding-left: 0.15rem;
    width: 1.25rem;
    height: 1.25rem;

    border-radius: 50%;
    border: 2px solid #ffffff;
  }

  .value-proposition-card-popup {
    box-shadow: 0 0 30px -2px rgba(0, 0, 0, 0.75);

    background-color: #e5e1f9;
    border-radius: 10px;

    margin: 0 1rem;
    padding: 1rem;
    width: 16rem;
  }

  .value-proposition-card-popup-image {
    width: 77%;
  }

  .value-proposition-card-popup .left {
    display: inline-block;
    vertical-align: top;

    width: 20%;
  }

  .value-proposition-card-popup .right {
    display: inline-block;
    vertical-align: top;

    width: 80%;
  }

  .value-proposition-card .left {
    background: #fff;
  }

  .value-proposition-card .right {
    padding-top: 3em;
    padding-bottom: 3em;

    background: linear-gradient(
      90deg,
      rgba(29, 205, 161, 1) 0%,
      rgba(59, 61, 139, 1) 100%
    );
  }

  .value-proposition-card .wrapper {
    display: table;
  }

  .value-proposition-card .wrapper-content {
    display: table-cell;
    vertical-align: middle;
  }

  .value-proposition-card .titles {
    padding-bottom: 1.5em;
  }

  .value-proposition-card .leafs > div {
    margin-bottom: 1rem;
  }

  .value-proposition-card .leafs .leaf-wrapper {
    margin-bottom: 2rem;
    position: relative;
  }

  .value-proposition-card .info-trigger {
    cursor: pointer;
    user-select: none;

    color: #22106a;
    font-size: 0.75rem;
    font-weight: bold;
    line-height: 0.75rem;

    position: absolute;
    top: 0.7rem;
    right: 1.7rem;

    width: 1rem;
    height: 1rem;

    border-radius: 50%;
    border: 2px solid #22106a;
  }

  .value-proposition-card .title {
    color: #fff;
    font-size: 1.25em;
    letter-spacing: 0.02em;
    margin: auto;
  }

  .value-proposition-card .leaf {
    background-color: #fff;
    border-top-left-radius: 50px;
    border-bottom-right-radius: 50px;

    padding: 1rem 1.25rem;
  }

  .value-proposition-card .leaf.logos img {
    padding: 0.5rem;
  }

  .value-proposition-card .leaf.logos img.img-paypal {
    padding-left: 1.1rem;
    padding-right: 1.1rem;
  }

  .value-proposition-card .leaf.logos img.img-ideal {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .value-proposition-card .leaf p {
    margin-bottom: 0 !important;
  }

  .value-proposition-card .fees-title {
    color: #22106a;
    font-weight: 600;
    font-size: 1.15em;
  }

  .value-proposition-card .fees-subtitle {
    color: #22106a;
    font-size: 0.85em;
  }

  .value-proposition-card .fees-subtitle a {
    color: #22106a !important;
    text-decoration: underline !important;
  }

  .value-proposition-card .fees-value {
    color: #22106a;
    font-size: 1.5em;

    margin-top: 0.5rem !important;
  }

  .value-proposition-card .fees-value span {
    font-weight: bold;
  }

  .value-proposition-card .conversions-image {
    margin-top: 1rem;
  }
</style>
