import * as types from './mutation-types';

export default {
  [types.UPDATE_PAYMENT_METHODS_ORDER](state, payload) {
    Object.assign(state.module.paymentMethods, payload);
  },
};
