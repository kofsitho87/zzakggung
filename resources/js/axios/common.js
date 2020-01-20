import axios from "axios"
import store from "../store"
import router from "../router"

const instance = axios.create({
  baseURL: "/api/"
})




instance.interceptors.request.use(
  config => {
    config.headers.Authorization = `Bearer ${store.state.auth.user.token}`

    // if (config.method == "post") {
    //   var formData = new FormData()
    //   for (let key in config.data) {
    //     formData.append(key, config.data[key])
    //   }
    //   config.data = formData
    // }

    return config
  }
)

instance.interceptors.response.use(
  response => {
    //console.log(response)
    return response
  },
  error => {
    let {data, status} = error.response
    if(data.error && data.error == "token_expired"){
      router.replace("/admin/login")
      store.commit("CLEAR_LOGIN")
    }
    
    return Promise.reject(error)
  }
)

export default instance
