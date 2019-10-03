import getters from './getters';
import actions from './actions';
import mutations from './mutations';

const {store} = global;

const state = store.firebase;

export default {
  state,
  getters,
  actions,
  mutations,
};
