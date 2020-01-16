import axios from "../../axios/common"
//import axios from "axios"

const state = {
  user: {
    login: false,
    token: null,
    data: {}
  }
}

const mutations = {
  SET_LOGIN(state, {user, token}) {
    state.user.login = true
    state.user.token = token
    state.user.data = user
  },
  CLEAR_LOGIN(state) {
    console.log("CLEAR_LOGIN")
    state.user.data = {}
    state.user.login = false
    state.user.token = null
  },
  SET_TOKEN(state, token) {
    state.user.token = token
  },
  SET_USER(state, user) {
    state.user.data = user
  }
}

const actions = {
  async refresh({ commit, dispatch, state }) {
    try {
      const token = localStorage.getItem("user_2")
      const expire = localStorage.getItem("expire")
      if (new Date().getTime() > +expire || !token) {
        commit("CLEAR_LOGIN")
        dispatch("clearLocalStorage")
        return
      }
      //var response = await axios.post("/me", { user_2: decodeURI(token) })
      var response = await axios.post("/me", { user_2: token })
      if (response.data.success) {
        const user = response.data.success
        commit("SET_LOGIN")
        commit("SET_USER", user)
        commit("SET_TOKEN", token)
        // dispatch("initFcmPermission")
        //dispatch("checkOnline", user)
        dispatch("setLocalStorage", {
          user_2: token,
          expire: state.user.exipire
        })
      } else {
        commit("CLEAR_LOGIN")
        dispatch("clearLocalStorage")
        // dispatch("resetChatRooms")
      }
    } finally {
      //
    }
  },
  async login({ commit, dispatch, state }, payload) {
    try {
      const {data} = await axios.post("/auth/login", payload)
      if (data.success) {
        commit("SET_LOGIN", data)
      }
      return data
    } catch(e) {
      throw e
    }
  },
  async signup(_, payload) {
    try {
      var response
      response = await axios.post("/signup", payload)
      // 차후에 activate action에서 처리해야 하는 부분.
      // if (response.data.success) {
      //   dispatch("registUsersToFb", response.data.success)
      // }
      return response.data
    } finally {
      //
    }
  },
  async logout({ commit }) {
    try {
      commit("CLEAR_LOGIN")
    } finally {
      //
    }
  }
}

const getters = {
  user: state => {
    return state.user.data
  },
  token: state => {
    return state.user.token
  },
  isAuthenticated(state) {
    return state.user.login
  }
}

export default {
  state,
  mutations,
  actions,
  getters
}
