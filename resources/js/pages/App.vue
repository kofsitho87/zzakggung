<template>
  <div>
    <Header />
    <b-container
      fluid
      class="py-4"
    >
      <router-view />
    </b-container>
  </div>
</template>

<script>
import Header from "../components/Header"
export default {
  components: {
    Header
  },
  created(){
    this.$store.dispatch("getShopTypes")
  }
}
</script>

<style scoped></style>