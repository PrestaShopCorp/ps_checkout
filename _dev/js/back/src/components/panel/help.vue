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
      <i class="material-icons">help</i>
      {{ $t('panel.help.title') }}
    </template>

    <b-card-body>
      <div class="d-flex">
        <div class="left-block">
          <div class="module-desc d-flex mb-4">
            <div class="module-img mr-3">
              <img
                src="@/assets/images/logo.png"
                width="75"
                height="75"
                alt=""
              />
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
          <div class="text-center mb-4">
            <iframe
              width="560"
              height="315"
              :src="youtubeLink"
              frameborder="0"
              allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen
            ></iframe>
          </div>
          <div class="faq">
            <h1>{{ $t('panel.help.faq') }}</h1>
            <div class="separator my-3" />
            <template v-if="faq && faq.categories.length !== 0">
              <v-collapse-group
                class="my-3"
                v-for="(categorie, index) in faq.categories"
                :key="index"
                :only-one-active="true"
              >
                <h3 class="categorie-title">
                  {{ categorie.title }}
                </h3>
                <v-collapse-wrapper
                  :ref="index + '_' + i"
                  v-for="(item, i) in categorie.blocks"
                  :key="i"
                >
                  <div class="my-3 question" v-collapse-toggle>
                    <a href="#" @click.prevent>
                      <i class="material-icons">keyboard_arrow_right</i>
                      {{ item.question }}
                    </a>
                  </div>
                  <div class="answer" v-collapse-content>
                    {{ item.answer }}
                  </div>
                </v-collapse-wrapper>
              </v-collapse-group>
            </template>
            <template v-else>
              <b-alert variant="warning" show>
                <p>{{ $t('panel.help.noFaqAvailable') }}</p>
              </b-alert>
            </template>
          </div>
        </div>
        <div class="right-block">
          <div class="doc">
            <b class="text-muted">{{ $t('panel.help.needHelp') }}</b>
            <br />
            <b-button
              class="mt-3"
              :href="readmeUrl"
              target="_blank"
              variant="primary"
            >
              {{ $t('panel.help.downloadDoc') }}
            </b-button>
          </div>
          <div class="contact mt-4">
            <div>{{ $t('panel.help.couldntFindAnswer') }}</div>
            <div class="mt-2">
              <b-button
                v-if="isReady"
                variant="link"
                href="https://support.prestashop.com/hc/requests/new?ticket_form_id="
                target="_blank"
              >
                {{ $t('panel.help.contactUs') }}
                <i class="material-icons">arrow_right_alt</i>
              </b-button>
              <b-button
                v-else
                variant="link"
                href="mailto:support-checkout-download@prestashop.com"
                target="_blank"
              >
                {{ $t('panel.help.contactUs') }}
                <i class="material-icons">arrow_right_alt</i>
              </b-button>
            </div>
          </div>
        </div>
      </div>
    </b-card-body>
  </b-card>
</template>

<script>
  export default {
    computed: {
      isReady() {
        return this.$store.state.context.isReady;
      },
      faq() {
        return this.$store.state.context.faq;
      },
      readmeUrl() {
        return this.$store.state.context.readmeUrl;
      },
      youtubeLink() {
        return this.$store.state.context.youtubeInstallerLink;
      }
    },
    methods: {
      getElementUpdated(payload, index) {
        return this.$refs[index][0].status;
      }
    }
  };
</script>

<style scoped>
  .separator {
    height: 1px;
    opacity: 0.2;
    background: #6b868f;
    border-bottom: 2px solid #6b868f;
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
    background-color: #f7f7f7;
  }
  .answer {
    margin: 0px 15px 10px 15px;
    padding: 15px;
    background-color: #f7f7f7;
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
