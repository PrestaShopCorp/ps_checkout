import * as types from './mutation-types';

export default {
  [types.UPDATE_ACCOUNT](state, payload) {
    Object.assign(state.account, payload);
  },
};
