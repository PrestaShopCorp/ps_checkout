import getters from './getters';
import actions from './actions';
import mutations from './mutations';

const {store} = global;

const state = store.context;

export default {
  state,
  getters,
  actions,
  mutations,
};
