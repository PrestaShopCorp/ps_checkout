import actions from './actions';
import mutations from './mutations';

const {store} = global;

const state = store.config;

export default {
  state,
  mutations,
  actions,
};
