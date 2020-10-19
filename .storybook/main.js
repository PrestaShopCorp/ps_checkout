const path = require("path");
module.exports = {
  webpackFinal: async (config, { configtype }) => {
    config.resolve.alias = {
      ...config.resolve.alias,
      "@": path.resolve(__dirname, "../_dev/js/back/src")
    };
    return config;
  },
  stories: ["../_dev/js/back/src/**/*.stories.[tj]s"]
};
