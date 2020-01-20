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
    <b-alert
      v-if="errorForm != null"
      variant="danger"
      show
    >
      <h4 class="alert-heading">
        {{ errorForm.length }} {{ $t('panel.psx-form.errors') }}
      </h4>
      <ul>
        <li
          v-for="(text, key) in errorForm"
          :key="key"
          class="alert-text"
        >
          {{ text }}
        </li>
      </ul>
    </b-alert>

    <b-card
      no-body
      footer-class="d-flex"
    >
      <template v-slot:header>
        <i class="material-icons">account_box</i> {{ $t('panel.psx-form.additionalDetails') }}
      </template>

      <b-card-body>
        <h1 class="text-muted font-weight-light text-center">
          {{ $t('panel.psx-form.fillUp') }}
        </h1>

        <b-form>
          <b-col
            sm="12"
            md="10"
            lg="8"
            class="m-auto"
          >
            <b-card-title class="py-4">
              {{ $t('panel.psx-form.personalInformation') }}
            </b-card-title>

            <b-row>
              <b-col
                sm="12"
                md="12"
                lg="12"
              >
                <b-form-group>
                  <b-form-radio-group
                    v-model="form.business_contact_gender"
                    name="gender"
                  >
                    <b-form-radio value="Mr">
                      {{ $t('panel.psx-form.genderMr') }}
                    </b-form-radio>
                    <b-form-radio value="Ms">
                      {{ $t('panel.psx-form.genderMrs') }}
                    </b-form-radio>
                  </b-form-radio-group>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col
                sm="6"
                md="6"
                lg="6"
              >
                <b-form-group
                  :label="$t('panel.psx-form.firstName')"
                  label-for="firstName"
                >
                  <b-form-input
                    id="firstName"
                    v-model="form.business_contact_first_name"
                    :class="[form.business_contact_first_name != '' ? '' : 'has-danger']"
                  />
                </b-form-group>
              </b-col>
              <b-col
                sm="6"
                md="6"
                lg="6"
              >
                <b-form-group
                  :label="$t('panel.psx-form.lastName')"
                  label-for="lastName"
                >
                  <b-form-input
                    id="lastName"
                    v-model="form.business_contact_last_name"
                    :class="[form.business_contact_last_name != '' ? '' : 'has-danger']"
                  />
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col
                sm="6"
                md="6"
                lg="6"
              >
                <b-form-group
                  :label="$t('panel.psx-form.language')"
                  label-for="language"
                >
                  <b-form-select
                    id="language"
                    v-model="form.business_contact_language"
                    :class="[form.business_contact_language != '' ? '' : 'has-danger']"
                  >
                    <option
                      v-for="languageDetail in getLanguagesDetails"
                      :key="languageDetail.iso_code"
                      :value="languageDetail.iso_code"
                    >
                      {{ languageDetail.name }}
                    </option>
                  </b-form-select>
                </b-form-group>
              </b-col>
              <b-col
                sm="6"
                md="6"
                lg="6"
              >
                <b-form-group
                  :label="$t('panel.psx-form.qualification')"
                  label-for="qualification"
                >
                  <b-form-select
                    id="qualification"
                    v-model="form.qualification"
                  >
                    <option value="">
                      --
                    </option>
                    <option value="merchant">
                      {{ $t('panel.psx-form.merchant') }}
                    </option>
                    <option value="agency">
                      {{ $t('panel.psx-form.agency') }}
                    </option>
                    <option value="freelancer">
                      {{ $t('panel.psx-form.freelancer') }}
                    </option>
                  </b-form-select>
                </b-form-group>
              </b-col>
            </b-row>

            <b-card-title class="py-4">
              {{ $t('panel.psx-form.billingAddress') }}
            </b-card-title>

            <b-row>
              <b-col
                sm="12"
                md="12"
                lg="12"
              >
                <b-form-group
                  :label="$t('panel.psx-form.storeName')"
                  label-for="storeName"
                >
                  <b-form-input
                    id="storeName"
                    v-model="form.shop_name"
                    :class="[form.shop_name != '' ? '' : 'has-danger']"
                  />
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col
                sm="12"
                md="12"
                lg="12"
              >
                <b-form-group
                  :label="$t('panel.psx-form.address')"
                  label-for="address"
                >
                  <b-form-input
                    id="address"
                    v-model="form.business_address_street"
                    :class="[form.business_address_street != '' ? '' : 'has-danger']"
                  />
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col
                sm="6"
                md="6"
                lg="6"
              >
                <b-form-group
                  :label="$t('panel.psx-form.postCode')"
                  label-for="postCode"
                >
                  <b-form-input
                    id="postCode"
                    v-model="form.business_address_zip"
                    :class="[form.business_address_zip != '' ? '' : 'has-danger']"
                  />
                </b-form-group>
              </b-col>
              <b-col
                sm="6"
                md="6"
                lg="6"
              >
                <b-form-group
                  :label="$t('panel.psx-form.town')"
                  label-for="town"
                >
                  <b-form-input
                    id="town"
                    v-model="form.business_address_city"
                    :class="[form.business_address_city != '' ? '' : 'has-danger']"
                  />
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col
                sm="6"
                md="6"
                lg="6"
              >
                <b-form-group
                  :label="$t('panel.psx-form.country')"
                  label-for="country"
                >
                  <b-form-select
                    id="country"
                    v-model="form.business_address_country"
                    @change="onChangeCountry(form.business_address_country)"
                  >
                    <option
                      v-for="countryDetail in getCountriesDetails"
                      :key="countryDetail.iso"
                      :value="countryDetail.iso"
                    >
                      {{ countryDetail.name }}
                    </option>
                  </b-form-select>
                </b-form-group>
              </b-col>
              <b-col
                v-if="statesList != null"
                sm="6"
                md="6"
                lg="6"
              >
                <b-form-group
                  :label="$t('panel.psx-form.state')"
                  label-for="state"
                >
                  <b-form-select
                    id="state"
                    v-model="form.business_address_state"
                  >
                    <option
                      v-for="(value, key) in statesList"
                      :key="key"
                      :value="key"
                    >
                      {{ value }}
                    </option>
                  </b-form-select>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col
                sm="6"
                md="6"
                lg="6"
              >
                <b-form-group
                  :label="$t('panel.psx-form.businessPhone')"
                  label-for="phone-country"
                >
                  <b-input-group>
                    <b-col
                      sm="4"
                      md="4"
                      lg="4"
                      class="px-0"
                    >
                      <b-form-select
                        id="phone-country"
                        v-model="form.business_phone_country"
                      >
                        <option
                          v-for="countryDetail in getPhoneCountryCode"
                          :key="countryDetail.iso"
                          :value="countryDetail.code"
                        >
                          + {{ countryDetail.code }}
                        </option>
                      </b-form-select>
                    </b-col>
                    <b-col
                      sm="8"
                      md="8"
                      lg="8"
                      class="px-0"
                    >
                      <b-form-input
                        v-model="form.business_phone"
                        :class="[form.business_phone != '' ? '' : 'has-danger']"
                      />
                    </b-col>
                  </b-input-group>
                </b-form-group>
              </b-col>
            </b-row>

            <b-card-title class="py-4">
              {{ $t('panel.psx-form.businessInformation') }}
            </b-card-title>

            <b-row>
              <b-col
                sm="12"
                md="12"
                lg="12"
              >
                <b-form-group
                  :label="$t('panel.psx-form.website')"
                  label-for="website"
                >
                  <b-form-input
                    id="website"
                    v-model="form.business_website"
                    placeholder="https://your_website.extension"
                    :class="[form.business_website != '' ? '' : 'has-danger']"
                  />
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col
                sm="12"
                md="12"
                lg="12"
              >
                <b-form-group
                  :label="$t('panel.psx-form.companyTurnover')"
                  label-for="company-turnover"
                >
                  <b-form-select
                    id="company-turnover"
                    v-model="form.business_company_emr"
                  >
                    <option
                      v-for="(value, key) in getCompanyMonthyAverages"
                      :key="key"
                      :value="key"
                    >
                      {{ value }}
                    </option>
                  </b-form-select>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col
                sm="12"
                md="12"
                lg="12"
              >
                <b-form-group
                  :label="$t('panel.psx-form.businessCategory')"
                  label-for="business-category"
                >
                  <b-form-select
                    id="business-category"
                    v-model="form.business_category"
                    @change="onChangeCategory(form.business_category)"
                  >
                    <option
                      v-for="(value, key) in getCompanyCategories"
                      :key="key"
                      :value="key"
                    >
                      {{ value.title }}
                    </option>
                  </b-form-select>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col
                sm="12"
                md="12"
                lg="12"
              >
                <b-form-group
                  :label="$t('panel.psx-form.businessSubCategory')"
                  label-for="business-subcategory"
                >
                  <b-form-select
                    id="business-subcategory"
                    v-model="form.business_sub_category"
                  >
                    <option value="">
                      --
                    </option>
                    <option
                      v-for="(value, key) in subCategory"
                      :key="key"
                      :value="key"
                    >
                      {{ value }}
                    </option>
                  </b-form-select>
                </b-form-group>
              </b-col>
            </b-row>
          </b-col>
        </b-form>

        <b-col
          sm="12"
          md="10"
          lg="8"
          class="m-auto pt-5"
        >
          <p class="mb-0">
            {{ $t('panel.psx-form.privacyTextPart1') }}
          </p>
          <p>
            <b-link
              :href="$t('panel.psx-form.privacyLink')"
              target="_blank"
            >
              {{ $t('panel.psx-form.privacyTextPart2') }}
            </b-link>
          </p>
        </b-col>
      </b-card-body>

      <template v-slot:footer>
        <div class="container-fluid pl-0">
          <b-button
            variant="secondary"
            @click="back()"
          >
            {{ $t('panel.psx-form.back') }}
          </b-button>
        </div>
        <b-button
          variant="primary"
          @click="submitForm()"
        >
          {{ $t('panel.psx-form.continue') }}
        </b-button>
      </template>
    </b-card>
  </div>
</template>

<script>
  import {orderBy, uniqBy} from 'lodash';

  export default {
    name: 'PsxForm',
    data() {
      return {
        subCategory: null,
        statesList: null,
        errorForm: null,
        form: {
          business_contact_gender: 'Mr',
          business_contact_first_name: null,
          business_contact_last_name: null,
          business_contact_language: null,
          qualification: '',
          shop_name: null,
          business_address_street: null,
          business_address_zip: null,
          business_address_city: null,
          business_address_country: null,
          business_address_state: null,
          business_phone_country: '1',
          business_phone: null,
          business_website: null,
          business_company_emr: null,
          business_category: null,
          business_sub_category: '',
        },
      };
    },
    computed: {
      getLanguagesDetails() {
        return orderBy(this.$store.state.psx.languagesDetails, 'name');
      },
      getCountriesDetails() {
        return orderBy(this.$store.state.psx.countriesDetails, 'name');
      },
      getPhoneCountryCode() {
        return uniqBy(orderBy(this.$store.state.psx.countriesDetails, 'code'), 'code');
      },
      getCompanyMonthyAverages() {
        return this.$store.state.psx.businessDetails.business_company_emr;
      },
      getCompanyCategories() {
        return this.$store.state.psx.businessDetails.business_categories;
      },
    },
    methods: {
      submitForm() {
        this.$store.dispatch('psxSendData', this.form).then((response) => {
          if (response === true) {
            this.$store.dispatch('psxOnboarding', response);
            this.$router.push('/authentication');
          }
          this.errorForm = response;
          window.scrollTo({
            top: 0,
            left: 0,
            behavior: 'smooth',
          });
        });
      },
      back() {
        this.$store.dispatch('logOut').then(() => {
          this.$router.push('/authentication');
        });
      },
      onChangeCategory(categoryId) {
        const subCat = this.$store.getters.getBusinessCategories[categoryId].business_subcategories;
        this.subCategory = subCat;
      },
      onChangeCountry(countryCode) {
        this.statesList = this.$store.state.psx.countriesStatesDetails[countryCode];
        this.form.business_address_state = null;
      },
    },
  };
</script>

<style scoped>
  .d-flex {
    align-items: flex-start;
  }
  label.text-muted {
    font-size: 18px;
  }
  .md-radio {
    position: relative;
    margin: 0;
    margin: initial;
    text-align: left;
  }
  .md-radio input[type=radio] {
    outline: 0;
    display: none;
  }
  .md-radio label {
    margin-bottom: 0;
    padding-left: 25px;
  }
  i.md-radio-control:before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    width: 20px;
    height: 20px;
    background: #fff;
    border: 2px solid #bbcdd2;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.3s;
  }
  i.md-radio-control:after {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
  }
  input[type=radio].indeterminate + i.md-radio-control:after,
  input[type=radio]:checked + i.md-radio-control:after {
    background: #25b9d7;
    width: 10px;
    height: 10px;
    left: 5px;
    top: 5px;
    border-radius: 50%;
  }
  .md-radio input[type=radio].indeterminate+i.md-radio-control:before,
  .md-radio input[type=radio]:checked+i.md-radio-control:before {
    background: #fff;
    border: 2px solid #25b9d7;
  }
  #app .has-danger {
    border-color: #C45C67;
  }
  #privacy {
    font-size: 12px;
    text-align: justify;
  }
</style>
