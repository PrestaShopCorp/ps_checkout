import actions from './actions';
import mutations from './mutations';

const store = JSON.parse(global.store);

const state = store.psx;

export default {
  state,
  mutations,
  actions,
};
