import Vue from "vue"
import Vuex from "vuex"

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
    // createPersistedState({
    //   key: "chatRooms",
    //   paths: ["chat.chatRooms"],
    //   storage: window.localStorage
    // })
  ]
})
