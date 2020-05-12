<template>
  <b-navbar
    toggleable="lg"
    type="light"
    variant="white"
    class="border"
  >
    <b-container>
      <b-navbar-brand to="/admin">
        {{ process.env.APP_NAME }} 관리자
      </b-navbar-brand>

      <div
        v-if="!isAuthenticated"
        class="flex-grow-1"
      >
        <b-navbar-nav>
          <b-nav-item href="/">
            홈
          </b-nav-item>
        </b-navbar-nav>
      </div>
      <div
        v-else
        class="flex-grow-1"
      >
        <b-navbar-toggle target="nav-collapse" />
        <b-collapse
          id="nav-collapse"
          is-nav
        >
          <b-navbar-nav>
            <b-nav-item href="/">
              홈
            </b-nav-item>
            <b-nav-item :to="{name: 'AdminUsers'}">
              거래처관리
            </b-nav-item>
            <b-nav-item :to="{name: 'AdminOrders'}">
              주문내역관리
            </b-nav-item>
            <b-nav-item :to="{name: 'AdminProducts'}">
              상품관리
            </b-nav-item>
            <b-nav-item :to="{name: 'AdminShopTypes'}">
              거채처타입관리
            </b-nav-item>
            <b-nav-item :to="{name: 'AdminDelivery'}">
              배송사관리
            </b-nav-item>
            <b-nav-item :to="{name: 'AdminNotice'}">
              공지사항
            </b-nav-item>
            <b-nav-item :to="{name: 'AdminDB'}">
              디비관리자
            </b-nav-item>
            <b-nav-item :to="{name: 'AdminHistory'}">
              업데이트내역
            </b-nav-item>
          </b-navbar-nav>

          <!-- Right aligned nav items -->
          <b-navbar-nav class="ml-auto">
            <b-nav-item-dropdown right>
              <!-- Using 'button-content' slot -->
              <template v-slot:button-content>
                {{ user.name }}
              </template>
              <b-dropdown-item
                to="/admin/change_pw"
                class="font-size-1"
              >
                비밀번호 변경
              </b-dropdown-item>
              <b-dropdown-item
                @click="logoutAction"
                class="font-size-1"
              >
                로그아웃
              </b-dropdown-item>
            </b-nav-item-dropdown>
          </b-navbar-nav>
        </b-collapse>
      </div>
    </b-container>
  </b-navbar>
</template>

<script>
export default {
  data(){
    return {
      activeItem: 0
    }
  },
  computed: {
    user(){
      return this.$store.getters.user
    },
    isAuthenticated(){
      return this.$store.getters.isAuthenticated
    }
  },
  methods: {
    logoutAction(){
      if( confirm("정말 로그아웃 하시겠습니까?") ){
        this.$store.dispatch("logout")
        this.$router.replace({name: "AdminLogin"})
      }
    }
  }
}
</script>

<style>

</style>