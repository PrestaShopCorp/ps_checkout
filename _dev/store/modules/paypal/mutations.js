import * as types from './mutation-types';

export default {
  [types.UNLINK_ACCOUNT](state) {
    state.account.idMerchant = '';
    state.account.status = false;
  },
};
