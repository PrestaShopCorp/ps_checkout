import * as types from './mutation-types';

export default {
  [types.UPDATE_ROUNDING_SETTINGS_STATUS](state) {
    state.roundingSettingsIsCorrect = true;
  },
};
