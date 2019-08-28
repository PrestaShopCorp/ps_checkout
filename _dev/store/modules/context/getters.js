export default {
  adminController: state => state.prestashopCheckoutAjax,
  locale: state => state.language.locale,
  translations: state => state.translations,
  merchantIsFullyOnboarded: (state, getters) => getters.paypalOnboardingIsCompleted
      && getters.firebaseOnboardingIsCompleted
      && getters.psxOnboardingIsCompleted,
};
