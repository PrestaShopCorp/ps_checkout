<template>
  <form class="form form-horizontal">
    <div class="card">
      <h3 class="card-header">
        <i class="material-icons">help</i> {{ $t('panel.help.title') }}
      </h3>
      <div class="card-block row">
        <div class="card-text d-flex">
          <div class="left-block">
            <div class="module-desc d-flex mb-4">
              <div class="module-img mr-3">
                <img src="@/assets/images/logo.png" width="75" height="75" alt="">
              </div>
              <div>
                <b>This module allows you to:</b>
                <ul class="mt-3">
                  <li>fqfdqdfqf qdf dsqfqsdf dfqf qdf</li>
                  <li>sqfsqdfqdf sqdfqsdf</li>
                </ul>
              </div>

              <v-collapse-wrapper :active="false">
                <div class="header" v-collapse-toggle>
                  <b class="text-muted">test</b>
                </div>
                <div class="my-content" v-collapse-content>
                  qksdjfks jfjsq dfkj sldjf ksqjdlf jqsljdf lsqjdflk j
                </div>
              </v-collapse-wrapper>

            </div>
            <div class="faq">
              <h1>FAQ</h1>
              <div class="separator my-3" />
              <template v-if="faq && faq.categories.lenfth != 0">
                <v-collapse-group v-for="(categorie, index) in faq.categories" :key="index" :only-one-active="false">
                  <h2 class="categorie-title">{{ categorie.title }}</h2>
                  <v-collapse-wrapper v-for="(item, i) in categorie.blocks" :key="i">
                    <div class="header" v-collapse-toggle>
                      <b class="text-muted">{{ item.question }}</b>
                    </div>
                    <div class="my-content" v-collapse-content>
                      {{ item.answer }}
                    </div>
                  </v-collapse-wrapper>
                </v-collapse-group>
              </template>
            </div>
          </div>
          <div class="right-block">
            <div class="doc">
              <b class="text-muted">sqfd fkqsdkf qjsdlkfj qskdjf lsqkfqlkj ljfsqlk jdflqskjf</b>
              <br>
              <a href="#" class="btn btn-primary mt-3">test</a>
            </div>
            <div class="contact mt-4">
              <div>Couldn't find any answer to your question?</div>
              <div class="mt-2">
                <a href="https://www.paypal.com/dashboard" target="_blank">
                  Contact us on PrestaShop Addons <i class="material-icons">arrow_right_alt</i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</template>

<script>
  export default {
    computed: {
      faq() {
        return this.$store.state.context.faq;
      },
    },
  };
</script>

<style scoped>
.separator {
  height:1px;
  opacity: 0.2;
  background:#6B868F;
  border-bottom: 2px solid #6B868F;
}
.right-block {
  padding: 15px;
  min-width: 350px;
  text-align: center;
}
.doc {
  padding: 20px;
  background-color: #F7F7F7;
}
</style>
