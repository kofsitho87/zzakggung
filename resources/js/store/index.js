import Vue from "vue"
import Vuex from "vuex"
import createPersistedState from "vuex-persistedstate"

Vue.use(Vuex)

import auth from "./modules/auth"
import admin from "./modules/admin"

export default new Vuex.Store({
  strict: process.env.NODE_ENV !== "production",
  modules: {
    auth,
    admin
  },
  plugins: [
    createPersistedState({
      key: "auth",
      paths: ["auth.user"],
      storage: window.localStorage
    })
  ]
})
