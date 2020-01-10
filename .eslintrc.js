module.exports = {
  env: {
    browser: true,
    es6: true,
    node: true
  },
  extends: ["plugin:vue/strongly-recommended"],
  globals: {
    Atomics: "readonly",
    SharedArrayBuffer: "readonly"
  },
  parser: "vue-eslint-parser",
  parserOptions: {
    ecmaVersion: 2018,
    sourceType: "module",
    parser: "babel-eslint",
    ecmaVersion: 6
  },
  plugins: ["vue"],
  rules: {
    "no-console": process.env.NODE_ENV === "production" ? "error" : "off",
    "no-debugger": process.env.NODE_ENV === "production" ? "error" : "off",
    "vue/script-indent": ["error", 2, { baseIndent: 0 }],
    semi: [2, "never"],
    quotes: ["error", "double"]
  }
};
