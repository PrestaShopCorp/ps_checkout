import actions from './actions';
import mutations from './mutations';

const firebase = JSON.parse(firebaseAccount);

const state = {
  account: firebase,
};

export default {
  state,
  mutations,
  actions,
};
