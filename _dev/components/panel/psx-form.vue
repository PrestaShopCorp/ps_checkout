<template>
  <div>
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
                          <input name="gender" type="radio" value="male" v-model="form.gender">
                          <i class="md-radio-control" /> {{ $t('panel.psx-form.genderMr') }}
                        </label>
                      </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <div class="md-radio">
                        <label>
                          <input name="gender" type="radio" value="female" v-model="form.gender">
                          <i class="md-radio-control" /> {{ $t('panel.psx-form.genderMrs') }}
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.firstName') }}</label>
                        <input type="text" class="form-control" id="firstName" v-model="form.firstName">
                      </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.lastName') }}</label>
                        <input type="text" class="form-control" id="lastName" v-model="form.lastName">
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <label>{{ $t('panel.psx-form.nationality') }}</label>
                      <select v-model="form.nationality" class="form-control custom-select">
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
                        <input type="text" class="form-control" id="storeName" v-model="form.storeName">
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.address') }}</label>
                        <input type="text" class="form-control" id="address" v-model="form.address">
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.postCode') }}</label>
                        <input type="text" class="form-control" id="postCode" v-model="form.postCode">
                      </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.town') }}</label>
                        <input type="text" class="form-control" id="town" v-model="form.town">
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <label>{{ $t('panel.psx-form.country') }}</label>
                      <select v-model="form.country" class="form-control custom-select">
                      </select>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0">
                        <label>{{ $t('panel.psx-form.businessPhone') }}</label>
                      </div>
                      <div class="col-lg-4 col-md-4 col-sm-4 pl-0">
                        <select v-model="form.phoneCountry" class="form-control custom-select">
                        </select>
                      </div>
                      <div class="col-lg-8 col-md-8 col-sm-8 pl-0 pr-0">
                        <input type="text" class="form-control" id="phone" v-model="form.phoneNumber">
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                      <label>{{ $t('panel.psx-form.businessType') }}</label>
                      <select v-model="form.businessType" class="form-control custom-select">
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
                        <input type="text" class="form-control" id="website" v-model="form.website">
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.companySize') }}</label>
                        <select v-model="form.companySize" class="form-control custom-select">
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.businessCategory') }}</label>
                        <select v-model="form.businessCategory" class="form-control custom-select">
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row mt-3">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                      <div class="col-lg-12 col-md-12 col-sm-12 pl-0 pr-0">
                        <label>{{ $t('panel.psx-form.businessSubCategory') }}</label>
                        <select v-model="form.businessSubCategory" class="form-control custom-select">
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
  export default {
    name: 'PsxForm',
    data() {
      return {
        form: {
          business_contact_gender: 'male',
          business_contact_first_name: '',
          business_contact_last_name: '',
          business_contact_nationality: '',
          shop_name: '',
          business_address_street: '',
          business_address_zip: '',
          business_address_city: '',
          business_address_country: '',
          phoneCountry: '',
          business_phone: '',
          business_type: '',
          business_website: '',
          business_company_size: '',
          business_category: '',
          business_sub_category: '',
        },
      }
    },
    computed: {
      onboardingCompleted() {
        return this.$store.state.psx.onboardingCompleted;
      },
    },
    methods: {
      submitForm() {
        this.$store.dispatch('psxSendData', this.form).then(() => {
          this.$store.dispatch('psxOnboarding');
        });
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
</style>
