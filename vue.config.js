const path = require('path');

function resolve(dir) {
  return path.join(__dirname, dir);
}

module.exports = {
  chainWebpack: (config) => {
    config.resolve.alias.set('@', resolve('_dev'));
  },
  pages: {
    index: {
      // entry for the page
      entry: '_dev/main.js',
      filename: 'app.tpl',
    },
  },
  outputDir: 'views/app/',
  publicPath: 'modules/prestashoppayments/views/app/',
};
