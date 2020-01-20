import axios from "../../axios/common"
//import jwt from "jsonwebtoken"

const state = {
  shopTypes: [],
  deliveryProviders: []
}

const mutations = {
  SET_SHOP_TYPES(state, shopTypes) {
    state.shopTypes = shopTypes
  },
  CREATE_SHOP_TYPE(state, shopType) {
    state.shopTypes = [...state.shopTypes, shopType]
  },
  UPDATE_SHOP_TYPE(state, shopType) {
    let index = state.shopTypes.findIndex(row => row.id == shopType.id)
    if(index > -1){
      state.shopTypes[index] = shopType
    }
  },
  DELETE_SHOP_TYPE(state, shopType) {
    let index = state.shopTypes.findIndex(row => row.id == shopType.id)
    if(index > -1){
      state.shopTypes.splice(index, 1)
    }
  },
  SET_DELIVERY_PROVIDERS(state, deliveryProviders) {
    state.deliveryProviders = deliveryProviders
  }
}

const actions = {
  async post(_, { api, payload }) {
    try {
      let {data} = await axios.post(`/admin/${api}`, payload)
      if(!data.success){
        throw new Error(data.message)
      }
      return data.data
    } catch (e) {
      throw e
    }
  },
  async get(_, { api, payload }) {
    try {
      let {data} = await axios.get(`/admin/${api}`, {
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
      return await axios.put(`/admin/${api}`, payload)
    } catch (e) {
      throw e
    }
  },
  async delete(_, { api, payload }) {
    try {
      return await axios.delete(`/admin/${api}`, {
        params: payload
      })
    } catch (e) {
      throw e
    }
  },
  async getShopTypes({commit}){
    try {
      let {data} = await axios.get("/admin/config/shopTypes")
      // let shopTypes = data.data.shopTypes.map(row => {
      //   return {
      //     text: row.type,
      //     value: row.id
      //   }
      // })
      let shopTypes = data.data.shopTypes
      commit("SET_SHOP_TYPES", shopTypes)
    } catch (e) {
      throw e
    }
  },
  async deliveryProviders({commit}){
    try {
      let {data} = await axios.get("/admin/config/deliveryProviders")
      let deliveryProviders = data.data.providers.map(row => {
        return {
          text: row.name,
          value: row.id
        }
      })
      commit("SET_DELIVERY_PROVIDERS", deliveryProviders)
    } catch (e) {
      throw e
    }
  }
}

const getters = {
  shopTypes: state => {
    return state.shopTypes
  },
  deliveryProviders: state => {
    return state.deliveryProviders
  }
}

export default {
  state,
  mutations,
  actions,
  getters
}
