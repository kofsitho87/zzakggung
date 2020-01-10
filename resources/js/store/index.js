import Vue from "vue"
import Vuex from "vuex"

Vue.use(Vuex)

import auth from "./modules/auth"

export default new Vuex.Store({
  strict: process.env.NODE_ENV !== "production",
  modules: {
    auth,
  },
  plugins: [
    // createPersistedState({
    //   key: "chatRooms",
    //   paths: ["chat.chatRooms"],
    //   storage: window.localStorage
    // })
  ]
})
