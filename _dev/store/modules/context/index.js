import getters from './getters';

const store = JSON.parse(global.store);

const state = store.context;

export default {
  state,
  getters,
};
