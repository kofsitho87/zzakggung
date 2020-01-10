//import axios from "../../axios/common"
import axios from "axios"

const state = {
  user: {
    login: false,
    token: null,
    exipire: 3600,
    data: {}
  },
  social: {
    userID: "",
    accessToken: "",
    email: "",
    name: "",
    image: ""
  }
  // ,
  // signupType: "facebook" // google or facebook or null
}

const mutations = {
  SET_LOGIN(state) {
    state.user.login = true
  },
  CLEAR_LOGIN(state) {
    state.user.data = {}
    state.user.login = false
    state.user.token = null
  },
  SET_AUTH(state, auth) {
    state.social = auth
  },
  SET_TOKEN(state, token) {
    state.user.token = token
  },
  SET_USER(state, user) {
    state.user.data = user
  },
  SET_SIGNUP_TYPE(state, payload) {
    state.signupType = payload.type
  },
  SET_PROFILE(state, user) {
    console.log(user.nickname)
    state.user.data.nickname = user.nickname
  },
  SET_CLASS_RESERVED(state, reserved) {
    state.user.data.class_reserved = Object.assign({}, state.user.data.class_reserved, reserved) 
  },
  SET_SELECTED_SCHOOL_BY_SEARCH(state, school) {
    state.user.data.selected_school = school
  }
}

const actions = {
  setSelectedSchoolBySearch({ commit }, school){
    commit("SET_SELECTED_SCHOOL_BY_SEARCH", school)
  },
  setClassReserved({ commit }, reserved){
    commit("SET_CLASS_RESERVED", reserved)
  },
  async setProfile({ commit }, payload) {
    try {
      commit("SET_PROFILE", payload)
    } finally {
      //
    }
  },
  async getProfile(_, payload) {
    try {
      const response = await axios.post("/profile", payload)
      return response.data
    } finally {
      //
    }
  },
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
      var response = await axios.post("/login", payload)
      if (response.data.success) {
        const auth = response.data.success
        commit("SET_LOGIN")
        commit("SET_USER", auth)
        commit("SET_TOKEN", auth.user_2)
        // dispatch("registUsersToFb", auth)
        //dispatch("checkOnline", auth)
        // dispatch("initFcmPermission")
        //dispatch("myChatRooms")
        dispatch("setLocalStorage", {
          user_2: auth.user_2,
          expire: state.user.exipire
        })
        localStorage.setItem("view", auth.member_gb)
      }
      if (payload.snsKind !== null) {
        commit("SET_AUTH", payload)
      }
      return response.data
    } finally {
      //
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
  setSignUpType: ({ commit }, payload) => {
    commit("SET_SIGNUP_TYPE", payload)
  },
  async setFacebookUser({ commit, dispatch, state }, payload) {
    try {
      var response = await axios.post("/facebook-login", payload)
      if (response.data.success) {
        const auth = response.data.success
        state.user.token = auth.user_2
        commit("SET_LOGIN")
        commit("SET_USER", auth)
        commit("SET_TOKEN", auth.user_2)
        dispatch("setLocalStorage", {
          user_2: auth.user_2,
          expire: state.user.exipire
        })
        commit("SET_AUTH", payload)
        return response
      } else {
        throw new Error(response.data.error.message)
      }
    } finally {
      //
    }
  },
  async setGoogleUser({ commit, dispatch, state }, payload) {
    try {
      var response = await axios.post("/google-login", payload)
      if (response.data.success) {
        const auth = response.data.success
        state.user.token = auth.user_2
        commit("SET_LOGIN")
        commit("SET_USER", auth)
        commit("SET_TOKEN", auth.user_2)
        dispatch("setLocalStorage", {
          user_2: auth.user_2,
          expire: state.user.exipire
        })
      }
      commit("SET_AUTH", payload)
    } finally {
      //
    }
  },
  async logout({ commit, dispatch }) {
    try {
      // $cookies.remove('user_2')
      // var response = await axios.get('/logout')
      // const auth = response.data
      commit("CLEAR_LOGIN")
      dispatch("clearLocalStorage")
      // dispatch("resetChatRooms")
      //routes.replace("/")
    } finally {
      //
    }
  },
  async activate(_, payload) {
    try {
      var response = await axios.post("/activate", payload)
      return response.data
    } finally {
      //
    }
  },
  async findPassword(_, payload) {
    try {
      let response = await axios.post("/find-password", payload)
      return response.data
    } finally {
      //
    }
  },
  async recvPasswordLink(_, payload) {
    try {
      let response = await axios.post("/recv-password-link", payload)
      return response.data
    } finally {
      //
    }
  },
  async changePassword(_, payload) {
    try {
      let response = await axios.post("/change-password", payload)
      return response.data
    } finally {
      //
    }
  },
  async changePasswordLink(_, payload) {
    try {
      let response = await axios.post("/change-password-link", payload)
      return response.data
    } finally {
      //
    }
  },
  async findEmail(_, payload) {
    try {
      let response = await axios.post("/find-email", payload)
      return response.data
    } finally {
      //
    }
  },
  async changeEmail(_, payload) {
    try {
      let response = await axios.post("/change-email", payload)
      return response.data
    } finally {
      //
    }
  },
  async recvEmailKey(_, payload) {
    try {
      let response = await axios.post("/recv-email-key", payload)
      return response.data
    } finally {
      //
    }
  },
  async changeEmailKey(_, payload) {
    try {
      let response = await axios.post("/change-email-key", payload)
      return response.data
    } finally {
      //
    }
  },
  async dupEmail(_, payload) {
    try {
      var response = await axios.post("/dup-email", payload)
      return response.data
    } finally {
      //
    }
  },
  async changeNickname(_, payload) {
    try {
      let response = await axios.post("/change-nickname", payload)
      return response.data
    } finally {
      //
    }
  },
  async changeProfile(_, payload) {
    try {
      let response = await axios.post("/change-profile", payload)
      return response.data
    } finally {
      //
    }
  },
  async changeProfileImage(_, payload) {
    try {
      let response = await axios.post("/change-profile-image", payload)
      return response.data
    } finally {
      //
    }
  },
  async phoneAuth(_, payload) {
    try {
      var response = await axios.post("/phone-auth", payload)
      return response.data
    } finally {
      //
    }
  },
  async findId(_, payload) {
    try {
      var response = await axios.post("/find-id", payload)
      return response.data
    } finally {
      //
    }
  },
  async recvId(_, payload) {
    try {
      var response = await axios.post("/recv-id", payload)
      return response.data
    } finally {
      //
    }
  },
  async dupMemeberId(_, payload) {
    try {
      var response = await axios.post("/dup-member-id", payload)
      return response.data
    } finally {
      //
    }
  },
  async dupNickname(_, payload) {
    try {
      var response = await axios.post("/dup-nickname", payload)
      return response.data
    } finally {
      //
    }
  },
  async memberEduAdd(_, payload) {
    try {
      var response = await axios.post("/member-edu-add", payload)
      return response.data
    } finally {
      //
    }
  },
  async memberEduMod(_, payload) {
    try {
      var response = await axios.post("/member-edu-mod", payload)
      return response.data
    } finally {
      //
    }
  },
  async memberEduDel(_, payload) {
    try {
      var response = await axios.post("/member-edu-del", payload)
      return response.data
    } finally {
      //
    }
  },
  async withdraw(_, payload) {
    try {
      let response = await axios.post("/withdraw", payload)
      return response.data
    } finally {
      //
    }
  },
  setTmpMenteeView(){
    const view = localStorage.getItem("view")
    let target
    if(view){
      if(view == "멘티"){
        target ="멘토"
      }else if(view == "멘토"){
        target ="멘티"
      }
    }else{
      target ="멘티"
    }
    localStorage.setItem("view", target)
    return target
  },
  setLocalStorage(_, payload){
    localStorage.setItem("user_2", payload.user_2)
    localStorage.setItem(
      "expire",
      new Date().getTime() + Number.parseInt(payload.expire) * 1000
    )
  },
  clearLocalStorage(){
    localStorage.removeItem("user_2")
    localStorage.removeItem("expire")
    localStorage.removeItem("view")
  }
}

const getters = {
  user: state => {
    return state.user.data
  },
  social: state => {
    return state.social
  },
  signupType: state => {
    return state.signupType
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
