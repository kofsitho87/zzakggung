import axios from "axios"
import store from "../store/store"

import app from "../main"

const instance = axios.create({
  baseURL: "/api/"
})

instance.interceptors.request.use(
  config => {
    app.then(v => {
      v.$Progress.start()
    })

    // Do something before request is sent
    if (config.method == "post") {
      var formData = new FormData()
      for (let key in config.data) {
        formData.append(key, config.data[key])
      }
      if (store.state.auth.user && store.state.auth.user.token) {
        // add user_2
        formData.append("user_2", store.state.auth.user.token)
      }
      config.data = formData
    }
    return config
  },
  function(error) {
    // Do something with request error
    return Promise.reject(error)
  }
)

instance.interceptors.response.use(response => {
  app.then(v => {
    v.$Progress.finish()
  })
  return response
})

export default instance
