import Vue from "vue"
import VueRouter from "vue-router"
import store from "./store"

Vue.use(VueRouter)

import AdminHome from "./pages/admin/Home"
import AdminUsers from "./pages/admin/Users"
import AdminUser from "./pages/admin/User"
import AdminUserTrades from "./pages/admin/UserTrades"

import AdminOrders from "./pages/admin/Orders"

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
    },
    {
      path: "/users/:id",
      component: AdminUser,
      name: "AdminUser",
      meta: {
        title: "유저"
      }
    },
    {
      path: "/users/:id/trades",
      component: AdminUserTrades,
      name: "AdminUserTrades",
      meta: {
        title: "거래내역"
      }
    },
    {
      path: "/orders",
      component: AdminOrders,
      name: "adminOrders",
      meta: {
        title: "주문내역관리"
      }
    },
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