import Vue from "vue"
import VueRouter from "vue-router"
import store from "./store"

Vue.use(VueRouter)

import AdminLogin from "./pages/admin/Login"

import AdminHome from "./pages/admin/Home"
import AdminUsers from "./pages/admin/Users"
import AdminUser from "./pages/admin/User"
import AdminUserTrades from "./pages/admin/UserTrades"

import AdminOrders from "./pages/admin/Orders"
import AdminOrder from "./pages/admin/Order"

import AdminProducts from "./pages/admin/Products"
import AdminProduct from "./pages/admin/Product"

const requireAuth = (to, from, next) => {
  if (store.getters.isAuthenticated) return next()
  return next("/admin/login")
}

const router = new VueRouter({
  mode: "history",
  routes: [
    {
      path: "/admin/login",
      component: AdminLogin,
      name: "AdminLogin",
      meta: {
        title: "관리자 로그인"
      }
    },
    {
      path: "/admin",
      component: AdminHome,
      name: "adminHome",
      beforeEnter: requireAuth,
      meta: {
        title: "관리자"
      },
      children: [
        {
          path: "users",
          component: AdminUsers,
          name: "AdminUsers",
          meta: {
            title: "거래처관리"
          }
        },
        {
          path: "users/:id",
          component: AdminUser,
          name: "AdminUser",
          meta: {
            title: "유저"
          }
        },
        {
          path: "users/:id/trades",
          component: AdminUserTrades,
          name: "AdminUserTrades",
          meta: {
            title: "거래내역"
          }
        },
        {
          path: "orders",
          component: AdminOrders,
          name: "AdminOrders",
          meta: {
            title: "주문내역관리"
          }
        },
        {
          path: "orders/:id",
          component: AdminOrder,
          name: "AdminOrder",
          meta: {
            title: "주문내역상세"
          }
        },
        {
          path: "products",
          component: AdminProducts,
          name: "AdminProducts",
          meta: {
            title: "상품관리"
          }
        },
        {
          path: "products/:id",
          component: AdminProduct,
          name: "AdminProduct",
          meta: {
            title: "상품상세"
          }
        },
      ]
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