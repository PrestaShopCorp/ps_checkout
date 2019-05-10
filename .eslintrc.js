module.exports = {
  root: true,
  env: {
    node: true
  },
  'extends': [
    'eslint-config-prestashop',
    'plugin:vue/strongly-recommended',
    'plugin:vue/recommended'
  ],
  plugins: [
    'import',
    'vue'
  ],
  rules: {
    indent: ['error', 2],
    'vue/singleline-html-element-content-newline': 'off',
    'vue/max-attributes-per-line': 'off',
    'no-console': 'off',
    'no-debugger': 'off'
  },
}