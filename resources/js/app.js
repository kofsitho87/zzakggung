
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

//require('./bootstrap');

//window.Vue = require("vue")

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key)))

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// import vueXlsxTable from 'vue-xlsx-table';
// Vue.use(vueXlsxTable, {rABS: false});

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
