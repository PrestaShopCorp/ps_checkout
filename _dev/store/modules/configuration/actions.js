import * as types from './mutation-types';
import ajax from '@/requests/ajax.js';

export default {
  updatePaymentMethods({commit, getters}, payload) {
    return ajax({
      url: getters.adminController,
      action: 'UpdatePaymentMethodsOrder',
      data: {
        paymentMethods: JSON.stringify(payload.paymentMethods),
      },
    }).then(() => {
      commit(types.UPDATE_PAYMENT_METHODS_ORDER, payload.paymentMethods);
      return Promise.resolve(true);
    });
  },

  updatePaymentMode({commit, getters}, payload) {
    return ajax({
      url: getters.adminController,
      action: 'UpdatePaymentMode',
      data: {
        paymentMode: payload,
      },
    }).then(() => {
      commit(types.UPDATE_PAYMENT_MODE, payload);
      return Promise.resolve(true);
    });
  },

  updateCaptureMode({commit, getters}, payload) {
    return ajax({
      url: getters.adminController,
      action: 'UpdateCaptureMode',
      data: {
        captureMode: payload,
      },
    }).then(() => {
      commit(types.UPDATE_CAPTURE_MODE, payload);
      return Promise.resolve(true);
    });
  },
};
