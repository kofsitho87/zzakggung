<template>
  <!-- <vs-navbar
    v-model="activeItem"
    class="nabarx"
  >
    <div slot="title">
      <vs-navbar-title>
        도매신 관리자
      </vs-navbar-title>
    </div>

    <vs-navbar-item index="0">
      <router-link :to="{name: 'adminUsers'}">
        거래처관리
      </router-link>
    </vs-navbar-item>
    <vs-navbar-item index="1">
      <a href="#">News</a>
    </vs-navbar-item>
    <vs-navbar-item index="2">
      <a href="#">Update</a>
    </vs-navbar-item>
  </vs-navbar> -->
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
        <b-nav-item :to="{name: 'adminUsers'}">
          거래처관리
        </b-nav-item>
        <b-nav-item
          href="#"
          disabled
        >
          Disabled
        </b-nav-item>
      </b-navbar-nav>

      <!-- Right aligned nav items -->
      <b-navbar-nav class="ml-auto">
        <b-nav-item-dropdown
          text="Lang"
          right
        >
          <b-dropdown-item href="#">
            EN
          </b-dropdown-item>
          <b-dropdown-item href="#">
            ES
          </b-dropdown-item>
          <b-dropdown-item href="#">
            RU
          </b-dropdown-item>
          <b-dropdown-item href="#">
            FA
          </b-dropdown-item>
        </b-nav-item-dropdown>

        <b-nav-item-dropdown right>
          <!-- Using 'button-content' slot -->
          <template v-slot:button-content>
            <em>User</em>
          </template>
          <b-dropdown-item href="#">
            Profile
          </b-dropdown-item>
          <b-dropdown-item href="#">
            Sign Out
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
  }
}
</script>

<style>

</style>