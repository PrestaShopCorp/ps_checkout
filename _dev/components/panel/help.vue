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
                <b>{{ $t('panel.help.allowsYou') }}</b>
                <ul class="mt-3">
                  <li>{{ $t('panel.help.tip1') }}</li>
                  <li>{{ $t('panel.help.tip2') }}</li>
                  <li>{{ $t('panel.help.tip3') }}</li>
                  <li>{{ $t('panel.help.tip4') }}</li>
                </ul>
              </div>
            </div>
            <div class="faq">
              <h1>FAQ</h1>
              <div class="separator my-3" />
              <template v-if="faq && faq.categories.lenfth != 0">
                <v-collapse-group
                  class="my-3"
                  v-for="(categorie, index) in faq.categories"
                  :key="index"
                  :only-one-active="true"
                >
                  <h3 class="categorie-title">{{ categorie.title }}</h3>
                  <v-collapse-wrapper
                    :ref="index+'_'+i" v-for="(item, i) in categorie.blocks"
                    :key="i"
                  >
                    <div class="my-3 question" v-collapse-toggle>
                      <a href="#" @click.prevent>
                        <i class="material-icons">keyboard_arrow_right</i> {{ item.question }}
                      </a>
                    </div>
                    <div class="answer" v-collapse-content>
                      {{ item.answer }}
                    </div>
                  </v-collapse-wrapper>
                </v-collapse-group>
              </template>
              <template v-else>
                <PSAlert :alert-type="ALERT_TYPE_WARNING">
                  <p>{{ $t('panel.help.noFaqAvailable') }}</p>
                </PSAlert>
              </template>
            </div>
          </div>
          <div class="right-block">
            <div class="doc">
              <b class="text-muted">{{ $t('panel.help.needHelp') }}</b>
              <br>
              <a :href="readmeUrl" target="_blank" class="btn btn-primary mt-3">
                {{ $t('panel.help.downloadDoc') }}
              </a>
            </div>
            <div class="contact mt-4">
              <div>{{ $t('panel.help.couldntFindAnswer') }}</div>
              <div class="mt-2">
                <a
                  href="https://support.prestashop.com/hc/requests/new?ticket_form_id="
                  target="_blank"
                  v-if="isReady"
                >
                  {{ $t('panel.help.contactUs') }} <i class="material-icons">arrow_right_alt</i>
                </a>
                <a v-else href="mailto:support-checkout-download@prestashop.com" target="_blank">
                  {{ $t('panel.help.contactUs') }} <i class="material-icons">arrow_right_alt</i>
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
  import PSAlert from '@/components/form/alert';
  import {ALERT_TYPE_WARNING} from '@/lib/alert';

  export default {
    components: {
      PSAlert,
    },
    computed: {
      ALERT_TYPE_WARNING: () => ALERT_TYPE_WARNING,
      isReady() {
        return this.$store.state.context.isReady;
      },
      faq() {
        return this.$store.state.context.faq;
      },
      readmeUrl() {
        return this.$store.state.context.readmeUrl;
      },
    },
    methods: {
      getElementUpdated(payload, index) {
        return this.$refs[index][0].status;
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
.left-block {
  flex-grow: 1;
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
.answer {
  margin: 0px 15px 10px 15px;
  padding: 15px;
  background-color: #F7F7F7;
}
.icon-expand {
  transform: rotate(90deg);
  transition: all 0.3s;
}
.v-collapse-content {
  display: none;
}
.v-collapse-content-end {
  display: block;
}
</style>
