<template>
  <div>
    <Header />
    <b-container class="py-4">
      <router-view />
    </b-container>
  </div>
</template>

<script>
import Header from "../components/Header"
export default {
  components: {
    Header
  }
}
</script>

<style scoped></style>