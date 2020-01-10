import Vue from "vue"
import VueRouter from "vue-router"
import store from "./store"

Vue.use(VueRouter)

import AdminHome from "./pages/admin/Home"
import AdminUsers from "./pages/admin/Users"

const requireAuth = (to, from, next) => {
  if (store.getters.isAuthenticated) return next()
  return next("/login")
}

const router = new VueRouter({
  mode: "history",
  routes: [
    {
      path: "/",
      component: AdminHome,
      name: "adminHome",
      meta: {
        title: "관리자"
      }
    },
    {
      path: "/users",
      component: AdminUsers,
      name: "adminUsers",
      meta: {
        title: "거래처관리"
      }
    }
  ]
})

router.beforeEach((to, from, next) => {
  document.title = to.meta.title
  if (to.name == "Login" && store.getters.isAuthenticated) {
    return next("/")
  }
  return next()
})

export default router