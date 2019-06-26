import * as types from './mutation-types';

export default {
  [types.UPDATE_PAYMENT_METHODS_ORDER](state, payload) {
    Object.assign(state.module.paymentMethods, payload);
  },
  [types.UPDATE_PAYMENT_MODE](state, payload) {
    state.module.paymentMode = payload;
  },
  [types.UPDATE_CAPTURE_MODE](state, payload) {
    state.module.captureMode = payload;
  },
};
