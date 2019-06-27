<template>
  <form class="form form-horizontal">
    <div class="card">
      <h3 class="card-header">
        <i class="material-icons">help</i> {{ $t('panel.help.title') }} {{ moduleName }}
      </h3>
      <div class="card-block row">
        <div class="card-text">
          <template v-if="faq && faq.categories.lenfth != 0">
            <div v-for="(categorie, index) in faq.categories" :key="index">
              <div>
                <h2 class="categorie-title">{{ categorie.title }}</h2>
                <div v-for="(item, index) in categorie.blocks" :key="index">
                  <!-- <template slot="title"> -->
                    <b>{{ item.question }}</b>
                  <!-- </template> -->
                  <div>{{ item.answer }}</div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </form>
</template>

<script>
  import {getFaq} from '@/requests/ajax.js';

  export default {
    data() {
      return {
        moduleName: 'PrestaShop Checkout',
        faq: null,
      };
    },
    props: {
      moduleKey: {
        type: String,
        required: true,
      },
      psVersion: {
        type: String,
        required: true,
      },
      isoCode: {
        type: String,
        required: true,
      },
    },
    created() {
      getFaq(this.moduleKey, this.psVersion, this.isoCode).then((response) => {
        if (response.categories) {
          this.faq = response;
        }
      });
    },
  };
</script>

<style scoped>
.line-separator {
  height:1px;
  opacity: 0.2;
  background:#6B868F;
  border-bottom: 2px solid #6B868F;
}
</style>
