<template>
  <b-navbar
    toggleable="lg"
    type="light"
    variant="info"
  >
    <b-navbar-brand href="#">
      도매신 관리자
    </b-navbar-brand>

    <b-navbar-toggle target="nav-collapse" />
    <b-collapse
      id="nav-collapse"
      is-nav
    >
      <b-navbar-nav>
        <b-nav-item :to="{name: 'AdminUsers'}">
          거래처관리
        </b-nav-item>
        <b-nav-item :to="{name: 'AdminOrders'}">
          주문내역관리
        </b-nav-item>
        <b-nav-item :to="{name: 'AdminProducts'}">
          상품관리
        </b-nav-item>
      </b-navbar-nav>

      <!-- Right aligned nav items -->
      <b-navbar-nav class="ml-auto">
        <b-nav-item-dropdown right>
          <!-- Using 'button-content' slot -->
          <template v-slot:button-content>
            <em>{{ user.name }}</em>
          </template>
          <b-dropdown-item href="#">
            비밀번호 변경
          </b-dropdown-item>
          <b-dropdown-item @click="logoutAction">
            로그아웃
          </b-dropdown-item>
        </b-nav-item-dropdown>
      </b-navbar-nav>
    </b-collapse>
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