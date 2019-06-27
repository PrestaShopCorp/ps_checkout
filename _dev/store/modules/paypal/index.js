import actions from './actions';
import mutations from './mutations';

const store = JSON.parse(global.store);

const state = {
  account: store.paypal.account,
};

export default {
  state,
  mutations,
  actions,
};
