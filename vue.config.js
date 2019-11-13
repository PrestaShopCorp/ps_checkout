/**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
const path = require('path');

const pages = {
  index: '_dev/main.js',
};

function resolve(dir) {
  return path.join(__dirname, dir);
}

module.exports = {
  devServer: {
    host: 'localhost',
    port: '8080',
    disableHostCheck: true,
  },
  chainWebpack: (config) => {
    Object.keys(pages).forEach((page) => {
      if (process.env.NODE_ENV === 'production') {
        // Avoid index.html to be created
        config.plugins.delete(`html-${page}`);
        config.plugins.delete(`preload-${page}`);
        config.plugins.delete(`prefetch-${page}`);
      }
    });
    config.resolve.alias.set('@', resolve('_dev'));
  },
  pages,
  filenameHashing: false,
  outputDir: 'views/',
  assetsDir: process.env.NODE_ENV === 'production'
    ? ''
    : '../modules/ps_checkout/views/',
  publicPath: process.env.NODE_ENV === 'production'
    ? '../modules/ps_checkout/views/'
    : './',
};
