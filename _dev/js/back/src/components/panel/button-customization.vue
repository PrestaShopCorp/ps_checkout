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
      <i class="material-icons">brush</i>
      {{ $t('panel.button-customization.title') }}
    </template>

    <b-card-body class="py-0">
      <b-row>
        <b-col sm="6" class="col-customization py-4 px-4">
          <div class="section-title mt-3">
            1. {{ $t('panel.button-customization.shape.title') }}
          </div>

          <div class="mt-3 mb-1">
            {{ $t('panel.button-customization.shape.select') }}
          </div>

          <b-form-radio-group
            id="shapes-radio"
            v-model="selectedShape"
            name="btn-shape"
            class="mt-3"
          >
            <!-- <b-form-radio class="custom-radio-form" value="pill">
              <b-button v-if="'pill' === selectedShape" class="btn btn-pill custom-radio-button-form" variant="primary">
                {{ $t('panel.button-customization.shape.pill') }}
              </b-button>
                <b-button v-else class="btn btn-pill custom-radio-button-form" variant="outline-secondary">
                  {{ $t('panel.button-customization.shape.pill') }}
                </b-button>
            </b-form-radio> -->
<!--
            <b-form-radio class="custom-radio-form" value="squared">
              <b-button class="btn btn-squared custom-radio-button-form" variant="outline-secondary">
                {{ $t('panel.button-customization.shape.squared') }}
              </b-button>
            </b-form-radio> -->

            <b-form-radio
              class="custom-radio-form"
              v-for="shape in shapes"
              :key="shape.value"
              :value="shape.value"
            >
              <span v-if="selectedShape === shape.value" v-html="shape.htmlActive"></span>

              <span v-else v-html="shape.html"></span>
            </b-form-radio>
          </b-form-radio-group>

          <div class="section-title mt-4">
            2. {{ $t('panel.button-customization.customize.title') }}
          </div>

          <div class="mt-3 mb-1">
            {{ $t('panel.button-customization.customize.label.select') }}
          </div>

          <b-form-radio-group
            id="labels-radio"
            v-model="selectedLabel"
            name="btn-label"
            @change="changeLabel"
          >
            <b-form-radio
              class="custom-radio-form"
              v-for="label in labels"
              :key="label.value"
              :value="label.value"
            >
              <span v-if="selectedLabel === label.value">
                <b>{{ label.text }}</b>
              </span>

              <span v-else>
                {{ label.text }}
              </span>
            </b-form-radio>
          </b-form-radio-group>

          <div class="mt-4 mb-1">
            {{ $t('panel.button-customization.customize.color.select') }}
          </div>

          <b-form-radio-group
            id="colors-radio"
            v-model="selectedColor"
            name="btn-color"
            class="mt-2"
          >
            <b-form-radio
              class="custom-radio-form"
              v-for="color in colors"
              :key="color.value"
              :value="color.value"
            >
              <span v-if="selectedColor === color.value">
                <b><span v-html="color.html"></span></b>
              </span>

              <span v-else>
                <span v-html="color.html"></span>
              </span>
            </b-form-radio>
          </b-form-radio-group>

          <div class="text-center mt-4">
            <b-button @click="save">
              {{ $t('panel.button-customization.customize.save') }}
            </b-button>
          </div>

          <div id="tips" class="mt-5">
            <div class="d-flex">
              <i class="material-icons tips-logo mr-1">
                emoji_objects
              </i>

              <div class="tips-title">
                <b>
                  {{ $t('panel.button-customization.customize.tips.title') }}
                </b>
              </div>

              <i
                id="close-tips"
                class="material-icons ml-auto"
                @click="closeTips"
              >
                close
              </i>
            </div>

            <span class="ml-4">
              {{ $t('panel.button-customization.customize.tips.content') }}
            </span>
          </div>
        </b-col>

        <b-col sm="6" class="py-4 px-4">
          <div class="section-title mt-3">
            {{ $t('panel.button-customization.preview.title') }} :
          </div>

          <div class="mt-3 mb-1">
            {{ $t('panel.button-customization.preview.paypal-button') }}
          </div>

          <b-button
            id="paypal-btn-preview"
            :class="[classBtnPreview, classBtnPaypalPreview]"
          >
            <span
              id="paypal-text-before"
              class="paypal-text mr-1"
              :class="[classPaypalTextBefore, classPaypalTextColor]"
            >
              {{ getText(labelValue) }}
            </span>

            <img
              id="paypal-logo"
              :src="getPaypalLogo()"
              alt="PayPal"
              class="btn-logo"
            />

            <span
              id="paypal-text-after"
              class="paypal-text ml-1"
              :class="[classPaypalTextAfter, classPaypalTextColor]"
            >
              {{ getText(labelValue) }}
            </span>
          </b-button>

          <div id="local-payment-buttons">
            <div class="mt-3 mb-1">
              {{
                $t('panel.button-customization.preview.local-payment-buttons')
              }}
            </div>

            <b-row>
              <b-col
                sm="6"
                class="mb-2"
                v-for="localPaymentMethod in localPaymentMethods"
                :key="localPaymentMethod.name"
              >
                <b-button class="btn-local-payment" :class="classBtnPreview">
                  <img
                    :src="getLPMLogo(localPaymentMethod.name)"
                    :alt="localPaymentMethod.label"
                    class="btn-logo"
                  />
                </b-button>
              </b-col>
            </b-row>

            <div class="notice mt-3">
              {{ $t('panel.button-customization.preview.notice') }}
            </div>
          </div>
        </b-col>
      </b-row>
    </b-card-body>
  </b-card>
</template>

<script>
  export default {
    data() {
      return {
        shapes: [
          {
            htmlActive:
              '<div class="btn btn-primary" style="width: 220px; height: 40px; margin-top: -6px; border-radius: 25px">' +
                this.$i18n.t('panel.button-customization.shape.pill') +
              '</div>',
            html:
              '<div class="btn" style="width: 220px; height: 40px; margin-top: -6px; border-radius: 25px; border: 1px solid #555555">' +
                this.$i18n.t('panel.button-customization.shape.pill') +
              '</div>',
            value: 'pill'
          },
          {
            htmlActive:
              '<div class="btn btn-primary" style="width: 220px; height: 40px; margin-top: -6px; border-radius: 5px">' +
                this.$i18n.t('panel.button-customization.shape.squared') +
              '</div>',
            html:
              '<div class="btn" style="width: 220px; height: 40px; margin-top: -6px; border-radius: 5px; border: 1px solid #555555">' +
                this.$i18n.t('panel.button-customization.shape.squared') +
              '</div>',
              value: 'squared'
            }
      ],
        localPaymentMethods: [
          { name: 'bancontact', label: 'Bancontact' },
          { name: 'eps', label: 'EPS' },
          { name: 'giropay', label: 'Giropay' },
          { name: 'ideal', label: 'iDEAL' },
          { name: 'mybank', label: 'MyBank' },
          { name: 'p24', label: 'Przelewy24' },
          { name: 'sofort', label: 'Sofort' }
        ],
        labels: [
          {
            text: this.$i18n.t(
              'panel.button-customization.customize.label.pay'
            ),
            value: 'pay'
          },
          {
            text: this.$i18n.t(
              'panel.button-customization.customize.label.checkout'
            ),
            value: 'checkout'
          },
          {
            text: this.$i18n.t(
              'panel.button-customization.customize.label.buynow'
            ),
            value: 'buynow'
          },
          {
            text: 'PayPal',
            value: 'paypal'
          }
        ],
        colors: [
          {
            html:
              '<div class="d-flex align-items-center" style="margin-top: -6px; margin-bottom: 15px;"><div class="mr-2" style="display: inline; width: 35px; height: 35px; border-radius: 5px; background: #ffc439;"></div>' +
              this.$i18n.t('panel.button-customization.customize.color.gold') +
              '</div>',
            value: 'gold'
          },
          {
            html:
              '<div class="d-flex align-items-center" style="margin-top: -6px; margin-bottom: 15px;"><div class="mr-2" style="display: inline; width: 35px; height: 35px; border-radius: 5px; background: #0070ba;"></div>' +
              this.$i18n.t('panel.button-customization.customize.color.blue') +
              '</div>',
            value: 'blue'
          },
          {
            html:
              '<div class="d-flex align-items-center" style="margin-top: -6px; margin-bottom: 15px;"><div class="mr-2" style="display: inline; width: 35px; height: 35px; border-radius: 5px; background: #eeeeee;"></div>' +
              this.$i18n.t(
                'panel.button-customization.customize.color.silver'
              ) +
              '</div>',
            value: 'silver'
          },
          {
            html:
              '<div class="d-flex align-items-center" style="margin-top: -6px; margin-bottom: 15px;"><div class="mr-2" style="display: inline; width: 35px; height: 35px; border-radius: 5px; background: #2c2e2f;"></div>' +
              this.$i18n.t('panel.button-customization.customize.color.black') +
              '</div>',
            value: 'black'
          },
          {
            html:
              '<div class="d-flex align-items-center" style="margin-top: -6px; margin-bottom: 15px;"><div class="mr-2" style="display: inline; width: 35px; height: 35px; border: 1px solid #555555; border-radius: 5px; background: #ffffff;"></div>' +
              this.$i18n.t('panel.button-customization.customize.color.white') +
              '</div>',
            value: 'white'
          }
        ],
        selectedShape: this.$store.state.configuration.paypalButton.shape,
        selectedLabel: this.$store.state.configuration.paypalButton.label,
        selectedColor: this.$store.state.configuration.paypalButton.color
      };
    },
    methods: {
      closeTips() {
        document.getElementById('tips').remove();
      },
      getPaypalLogo() {
        if ('blue' === this.selectedColor || 'black' === this.selectedColor) {
          return require('@/assets/images/paypal-logo-white.png');
        } else {
          return require('@/assets/images/paypal-logo.png');
        }
      },
      getLPMLogo(name) {
        if (!name) {
          return '';
        } else {
          return require('@/assets/images/funding-sources/' + name + '.svg');
        }
      },
      changeLabel(value) {
        var text = this.getText(value);
        var paypalTextBefore = document.getElementById('paypal-text-before');
        var paypalTextAfter = document.getElementById('paypal-text-after');

        if ('pay' === value) {
          paypalTextAfter.style.display = 'none';
          paypalTextBefore.style.display = 'inline';
          paypalTextBefore.innerHTML = text;
        } else {
          paypalTextBefore.style.display = 'none';
          paypalTextAfter.style.display = 'inline';
          paypalTextAfter.innerHTML = text;
        }
      },
      getText(label) {
        var text = null;

        this.labels.forEach(element => {
          if (label === element.value) {
            text = element.text;
          }
        });

        if ('paypal' === label) {
          text = null;
        }

        return text;
      },
      save() {
        this.$store.dispatch({
          type: 'savePaypalButtonConfiguration',
          configuration: {
            shape: this.selectedShape,
            label: this.selectedLabel,
            color: this.selectedColor
          }
        });
      }
    },
    computed: {
      classBtnPreview() {
        return {
          'btn-pill': 'pill' === this.selectedShape,
          'btn-squared': 'squared' === this.selectedShape
        };
      },
      classPaypalTextBefore() {
        return {
          'display-paypal-text': 'pay' === this.selectedLabel,
          'hide-paypal-text': 'pay' !== this.selectedLabel
        };
      },
      classPaypalTextAfter() {
        return {
          'display-paypal-text': 'pay' !== this.selectedLabel,
          'hide-paypal-text': 'pay' === this.selectedLabel
        };
      },
      classPaypalTextColor() {
        return {
          'paypal-text-black': 'blue' !== this.selectedColor && 'black' !== this.selectedColor,
          'paypal-text-white': 'blue' === this.selectedColor || 'black' === this.selectedColor
        };
      },
      classBtnPaypalPreview() {
        return {
          'btn-gold': 'gold' === this.selectedColor,
          'btn-blue': 'blue' === this.selectedColor,
          'btn-silver': 'silver' === this.selectedColor,
          'btn-black': 'black' === this.selectedColor,
          'btn-white': 'white' === this.selectedColor
        };
      },
      labelValue: {
        get() {
          return this.$store.state.configuration.paypalButton.label;
        },
        set(value) {
          this.selectedLabel = value;
        }
      }
    },
    updated() {
      var paypalText = document.getElementsByClassName('paypal-text');
      var color = '#000000';

      if ('blue' === this.selectedColor || 'black' === this.selectedColor) {
        color = '#ffffff';
      }

      for (var i = 0, length = paypalText.length; i < length; i++) {
        paypalText[i].style.selectedColor = color;
      }
    }
  };
</script>

<style scoped>
  .col-customization {
    background: #fafafa;
  }
  .section-title {
    font-size: 16px;
    font-weight: bold;
  }
  .custom-radio-form {
    width: 250px;
  }
  .custom-radio-button-form {
    margin-top: -6px !important;
  }
  .btn {
    width: 220px;
    height: 40px;
    border-style: none !important;
  }
  .btn-logo {
    height: 20px;
  }
  .btn-pill {
    border-radius: 25px !important;
  }
  .btn-squared {
    border-radius: 5px !important;
    border: 1px solid #555555 !important;
  }
  #tips {
    background: #beeaf3;
    border-radius: 5px;
    padding: 20px;
  }
  .tips-logo {
    color: #25b9d7;
  }
  #close-tips {
    cursor: pointer;
  }
  #btn-paypal-preview {
    border-style: none !important;
  }
  #local-payment-buttons {
    margin-top: 100px;
  }
  .btn-local-payment {
    background: #ededed !important;
    border-style: none !important;
  }
  .paypal-text {
    color: #000000;
    font-weight: normal;
  }
  .paypal-text-black {
    color: #000000;
  }
  .paypal-text-white {
    color: #ffffff;
  }
  .display-paypal-text {
    display: inline;
  }
  .hide-paypal-text {
    display: none;
  }
  .btn-gold {
    background: #ffc439 !important;
    border-style: none !important;
  }
  .btn-blue {
    background: #0070ba !important;
    border-style: none !important;
  }
  .btn-silver {
    background: #eeeeee !important;
    border-style: none !important;
  }
  .btn-black {
    background: #2c2e2f !important;
    border-style: none !important;
  }
  .btn-white {
    background: #ffffff !important;
    border: 1px solid #555555 !important;
  }
  .notice {
    font-size: 10px;
    color: #8e8e8e;
  }
</style>
