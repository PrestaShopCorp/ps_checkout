import Vue from 'vue';
import VueI18n from 'vue-i18n';
import store from '../store';

Vue.use(VueI18n);

const {locale} = store.getters;
const messages = store.getters.translations;

export default new VueI18n({
  locale,
  messages,
});
