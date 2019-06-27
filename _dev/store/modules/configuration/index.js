import actions from './actions';
import mutations from './mutations';

const store = JSON.parse(global.store);

const state = {
  module: store.config.module,
};

export default {
  state,
  mutations,
  actions,
};
