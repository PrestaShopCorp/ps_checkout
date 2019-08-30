<template>
  <div>
    <!-- errors -->
    <div v-if="errorForm != null">
      <div id="errors" class="ps-alert alert alert-danger" role="alert">
        <p>{{ errorForm.length }} {{ $t('panel.psx-form.errors') }}</p>
        <ul>
          <li v-for="(text, key) in errorForm" v-bind:key="key" class="alert-text">
            {{ text }}
          </li>
        </ul>
      </div> 
    </div>
    <form class="form form-horizontal">
      <div class="card">
        <h3 class="card-header">
          <i class="material-icons">settings</i> {{ $t('panel.psx-form.additionalDetails') }}
        </h3>
        <div class="card-block row">
          <div class="card-text">
            <div class="row mb-3">
              <div class="col-lg-12 col-md-12 col-sm-12 text-center">
                <h1 class="text-muted font-weight-light">{{ $t('panel.psx-form.fillUp') }}</h1>
              </div>
            </div>

            <!-- personal_informations -->
            <div id="personal_informations" class="row mb-1">
              <div class="col-lg-10 offset-lg-1 col-md-10 offset-md-1 col-sm-10 offset-sm-1 pl-0">
                <div class="col-lg-3 col-md-3 col-sm-3 pl-0 pr-0 text-right mt-3">
                  <label class="text-muted">{{ $t('panel.psx-form.personalInformation') }}</label>
                </div>
                <div class="col-lg-9 col-md-9 col-sm-9">
                  <div class="row mt-3">
                    <div class="col-lg-2 col-md-2 col-sm-2">
                      <div class="md-radio">
                        <label>
                          <input name="gender" type="radio" value="Mr" v-model="form.business_contact_gender">
                          <i class="md-radio-control" /> {{ $t('panel.psx-form.genderMr') }}
                        </label>
                      </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <div class="md-radio">
                        <label>
                          <input name="gender" type="radio" value="Ms" v-model="form.business_contact_gender">
                          <i class="md-radio-control" /> {{ $t('panel.psx-form.genderMrs') }}
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.firstName') }}</label>
                        <input type="text" class="form-control" id="firstName" 
                          v-model="form.business_contact_first_name" 
                          v-bind:class="[form.business_contact_first_name != '' ? '' : 'has-danger']">
                      </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.lastName') }}</label>
                        <input type="text" class="form-control" id="lastName" 
                          v-model="form.business_contact_last_name"
                          v-bind:class="[form.business_contact_last_name != '' ? '' : 'has-danger']">
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <label>{{ $t('panel.psx-form.nationality') }}</label>
                      <select class="form-control custom-select" 
                        v-model="form.business_contact_nationality"
                        v-bind:class="[form.business_contact_nationality != '' ? '' : 'has-danger']">
                        <option v-for="languageDetail in getLanguagesDetails" v-bind:key="languageDetail.iso_code" v-bind:value="languageDetail.iso_code">
                          {{ languageDetail.name }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- billing_address -->
            <div id="billing_address" class="row mb-1">
              <div class="col-lg-10 offset-lg-1 col-md-10 offset-md-1 col-sm-10 offset-sm-1 pl-0">
                <div class="col-lg-3 col-md-3 col-sm-3 pl-0 pr-0 text-right mt-3">
                  <label class="text-muted">{{ $t('panel.psx-form.billingAddress') }}</label>
                </div>
                <div class="col-lg-9 col-md-9 col-sm-9">
                  <div class="row mt-3">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.storeName') }}</label>
                        <input type="text" class="form-control" id="storeName" 
                          v-model="form.shop_name" 
                          v-bind:class="[form.shop_name != '' ? '' : 'has-danger']">
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.address') }}</label>
                        <input type="text" class="form-control" id="address" 
                          v-model="form.business_address_street" 
                          v-bind:class="[form.business_address_street != '' ? '' : 'has-danger']">
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.postCode') }}</label>
                        <input type="text" class="form-control" id="postCode" 
                          v-model="form.business_address_zip" 
                          v-bind:class="[form.business_address_zip != '' ? '' : 'has-danger']">
                      </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.town') }}</label>
                        <input type="text" class="form-control" id="town" 
                          v-model="form.business_address_city" 
                          v-bind:class="[form.business_address_city != '' ? '' : 'has-danger']">
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <label>{{ $t('panel.psx-form.country') }}</label>
                      <select v-model="form.business_address_country" class="form-control custom-select">
                        <option v-for="countryDetail in getCountriesDetails" v-bind:key="countryDetail.iso" v-bind:value="countryDetail.iso">
                          {{ countryDetail.name }}
                        </option>
                      </select>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0">
                        <label>{{ $t('panel.psx-form.businessPhone') }}</label>
                      </div>
                      <div class="col-lg-4 col-md-4 col-sm-4 pl-0">
                        <select class="form-control custom-select">
                          <option v-for="countryDetail in getPhoneCountryCode" v-bind:key="countryDetail.iso" v-bind:value="countryDetail.code">
                            + {{ countryDetail.code }}
                          </option>
                        </select>
                      </div>
                      <div class="col-lg-8 col-md-8 col-sm-8 pl-0 pr-0">
                        <input type="text" class="form-control" id="phone" 
                          v-model="form.business_phone"
                          v-bind:class="[form.business_phone != '' ? '' : 'has-danger']">
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                      <label>{{ $t('panel.psx-form.businessType') }}</label>
                      <select v-model="form.business_type" class="form-control custom-select">
                          <option v-for="(value, key) in getBusinessTypes" v-bind:key="key" v-bind:value="key">
                            {{ value }}
                          </option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- billing_information -->
            <div id="billing_information" class="row mb-1">
              <div class="col-lg-10 offset-lg-1 col-md-10 offset-md-1 col-sm-10 offset-sm-1 pl-0">
                <div class="col-lg-3 col-md-3 col-sm-3 pl-0 pr-0 text-right mt-3">
                  <label class="text-muted">{{ $t('panel.psx-form.businessInformation') }}</label>
                </div>
                <div class="col-lg-9 col-md-9 col-sm-9">
                  <div class="row mt-3">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.website') }}</label>
                        <input type="text" class="form-control" id="website" 
                          v-model="form.business_website"
                          v-bind:class="[form.business_website != '' ? '' : 'has-danger']">
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.companySize') }}</label>
                        <select v-model="form.business_company_size" class="form-control custom-select">
                          <option v-for="(value, key) in getCompanySizes" v-bind:key="key" v-bind:value="key">
                            {{ value }}
                          </option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.businessCategory') }}</label>
                        <select v-model="form.business_category" @change="onChangeCategory(form.business_category)" class="form-control custom-select">
                          <option v-for="(value, key) in getCompanyCategories" v-bind:key="key" v-bind:value="key">
                            {{ value.title }}
                          </option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.businessSubCategory') }}</label>
                        <select v-model="form.business_sub_category" class="form-control custom-select">
                          <option v-for="(value, key) in subCategory" v-bind:key="key" v-bind:value="key">
                            {{ value }}
                          </option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer text-right">
          <a class="btn btn-primary text-capitalize" href="#" @click.prevent="submitForm()">{{ $t('panel.psx-form.continue') }}</a>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
  import { orderBy, uniqBy } from 'lodash';

  export default {
    name: 'PsxForm',
    data() {
      return {
        subCategory: null,
        errorForm: null,
        form: {
          business_contact_gender: 'Mr',
          business_contact_first_name: null,
          business_contact_last_name: null,
          business_contact_nationality: null,
          shop_name: null,
          business_address_street: null,
          business_address_zip: null,
          business_address_city: null,
          business_address_country: null,
          business_phone: null,
          business_type: null,
          business_website: null,
          business_company_size: null,
          business_category: null,
          business_sub_category: null,
        },
      };
    },
    computed: {
      getLanguagesDetails() {
        return _.orderBy(this.$store.state.psx.languagesDetails, 'name');
      },
      getCountriesDetails() {
        return _.orderBy(this.$store.state.psx.countriesDetails, 'name'); 
      },
      getPhoneCountryCode() {
        return _.uniqBy(_.orderBy(this.$store.state.psx.countriesDetails, 'code'), 'code');
      },
      getBusinessTypes() {
        return this.$store.state.psx.businessDetails.business_types;
      },
      getCompanySizes() {
        return this.$store.state.psx.businessDetails.company_sizes;
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
        });
      },
      onChangeCategory(categoryId) {
        this.subCategory = this.$store.state.psx.businessDetails.business_categories[categoryId].business_subcategories;
      },
    },
  };
</script>

<style scoped>
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
</style>
