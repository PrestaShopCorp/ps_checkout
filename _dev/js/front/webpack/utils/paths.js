const { join } = require('path');

const MODULE_DIR = join(__dirname, '../../../../../');
const PROJECT_DIR = join(__dirname, '../../');

const INPUT_FILE = join(PROJECT_DIR, 'src/index.js');
const OUTPUT_FOLDER = join(MODULE_DIR, 'views/js');
const OUTPUT_LICENSES = join(MODULE_DIR, 'views/js/front.licenses.json');

module.exports = {
  MODULE_DIR,
  PROJECT_DIR,
  INPUT_FILE,
  OUTPUT_FOLDER,
  OUTPUT_LICENSES
};
