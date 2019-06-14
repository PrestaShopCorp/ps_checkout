import * as types from './mutation-types';
import {ajax} from '@/requests/ajax.js';

export default {
  updatePaymentMethods({commit}, payload) {
    console.log(payload.paymentMethods);
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
};
