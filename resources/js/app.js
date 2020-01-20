// try {
//   window.$ = window.jQuery = require("jquery")
// } catch (e) {}


import Vue from "vue"
import VueBootstrap from "bootstrap-vue"
//import Vuesax from "vuesax"
import Vuelidate from "vuelidate"
import Notifications from "vue-notification"
import CustomFilters from "./plugins/CustomFilters"

import "bootstrap/dist/css/bootstrap.css"
import "bootstrap-vue/dist/bootstrap-vue.css"
//import "vuesax/dist/vuesax.css"

import "./assets/sass/custom.scss"

import router from "./router"
import store from "./store"

Vue.use(VueBootstrap)
//Vue.use(Vuesax)
Vue.use(Vuelidate)
Vue.use(CustomFilters)
Vue.use(Notifications)

import App from "./pages/App"
new Vue({
  store,
  router,
  render: h => h(App)
}).$mount("#app")
