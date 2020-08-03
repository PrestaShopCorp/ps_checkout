module.exports = {
  entry: {
    front: "./_dev/js/front/index.js"
  },
  output: {
    filename: "[name].js",
    path: __dirname + "/views/js"
  },
  mode: "production"
};
