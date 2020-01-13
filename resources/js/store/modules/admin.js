import axios from "axios"
//import jwt from "jsonwebtoken"

const state = {
  shopTypes: []
}

const mutations = {
  SET_SHOP_TYPES(state, shopTypes) {
    state.shopTypes = shopTypes
  }
}

const actions = {
  async post(_, { api, payload }) {
    try {
      return await axios.post(`/api/admin/${api}`, payload)
    } catch (e) {
      throw e
    }
  },
  async get(_, { api, payload }) {
    try {
      let {data} = await axios.get(`/api/admin/${api}`, {
        params: payload
      })
      if(!data.success){
        throw new Error(data.message)
      }
      
      return data.data
    } catch (e) {
      throw e
    }
  },
  async put(_, { api, payload }) {
    try {
      return await axios.put(`/api/admin/${api}`, payload)
    } catch (e) {
      throw e
    }
  },
  async delete(_, { api, payload }) {
    try {
      return await axios.delete(`/api/admin/${api}`, {
        params: payload
      })
    } catch (e) {
      throw e
    }
  },
  async getShopTypes({commit}){
    try {
      let {data} = await axios.get("/api/admin/config/shopTypes")
      let shopTypes = data.data.shopTypes.map(row => {
        return {
          text: row.type,
          value: row.id
        }
      })
      commit("SET_SHOP_TYPES", shopTypes)
    } catch (e) {
      throw e
    }
  }
}

const getters = {
  shopTypes: state => {
    return state.shopTypes
  },
}

export default {
  state,
  mutations,
  actions,
  getters
}
