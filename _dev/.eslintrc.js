module.exports = {
  root: true,
  env: {
    node: true,
  },
  parserOptions: {
    parser: 'babel-eslint',
  },
  extends: [
    'prestashop',
    'plugin:vue/strongly-recommended',
  ],
  plugins: [
    'import',
    'vue',
  ],
  rules: {
    'no-param-reassign': ['error', {props: false}],
    'prefer-destructuring': ['error', {object: true, array: false}],
    'vue/script-indent': [
      'error',
      2,
      {
        baseIndent: 1,
        switchCase: 1,
      },
    ],
  },
  overrides: [
    {
      files: ['*.vue'],
      rules: {
        indent: 0,
      },
    },
  ],
};
