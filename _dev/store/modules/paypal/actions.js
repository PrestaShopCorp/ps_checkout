import * as types from './mutation-types';
import {ajax} from '@/requests/ajax.js';

export default {
  unlink({commit}) {
    return ajax({
      action: 'UnlinkPaypal',
    }).then(() => {
      commit(types.UNLINK_ACCOUNT);
      return Promise.resolve(true);
    });
  },
};
