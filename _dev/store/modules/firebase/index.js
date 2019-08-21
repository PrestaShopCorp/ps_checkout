import getters from './getters';

const store = JSON.parse(global.store);

const state = store.firebase;

export default {
  state,
  getters,
};
