const path = require("path");
module.exports = {
  chainWebpack: config => {
    config.optimization.delete("splitChunks");
    config.plugins.delete("html");
    config.plugins.delete("preload");
    config.plugins.delete("prefetch");
    config.resolve.alias.set("@", path.resolve(__dirname, "_dev/js/back/src"));
  },
  css: {
    extract: false
  },
  productionSourceMap: false,
  filenameHashing: false,
  outputDir: "./views/js/back",
  assetsDir: "",
  publicPath: "../modules/ps_checkout/views/"
};
