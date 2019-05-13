const path = require('path');

const pages = {
  index: '_dev/main.js',
};

function resolve(dir) {
  return path.join(__dirname, dir);
}

module.exports = {
  chainWebpack: (config) => {
    Object.keys(pages).forEach((page) => {
      // Avoid index.html to be created
      config.plugins.delete(`html-${page}`);
      config.plugins.delete(`preload-${page}`);
      config.plugins.delete(`prefetch-${page}`);
    });
    config.resolve.alias.set('@', resolve('_dev'));
  },
  pages,
  filenameHashing: false,
  outputDir: 'views/',
  publicPath: '../modules/ps_checkout/views/',
};
