import * as types from './mutation-types';
import {ajax} from '@/requests/ajax.js';

export default {
  updatePaymentMethods({commit}, payload) {
    return ajax({
      action: 'UpdatePaymentMethodsOrder',
      data: {
        paymentMethods: JSON.stringify(payload.paymentMethods),
      },
    }).then(() => {
      commit(types.UPDATE_PAYMENT_METHODS_ORDER, payload.paymentMethods);
      return Promise.resolve(true);
    });
  },

  updatePaymentMode({commit}, payload) {
    return ajax({
      action: 'UpdatePaymentMode',
      data: {
        paymentMode: payload,
      },
    }).then(() => {
      commit(types.UPDATE_PAYMENT_MODE, payload);
      return Promise.resolve(true);
    });
  },

  updateCaptureMode({commit}, payload) {
    return ajax({
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
