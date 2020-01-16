<template>
  <div>
    <b-card header="AL553">
      <b-form v-if="product">
        <b-form-group
          label="상품이름"
          label-cols-sm="2"
          label-cols-lg="2"
        >
          <b-form-input
            v-model="product.name"
            placeholder="상품이름"
          />
        </b-form-group>
      </b-form>
      <template v-slot:footer>
        footer
      </template>
    </b-card>
  </div>
</template>

<script>
export default {
  data(){
    return {
      isLoading: false,
      id: this.$route.params.id,
      product: null
    }
  },
  mounted(){
    this.getData()
  },
  methods: {
    async getData(){
      this.isLoading = true
      try {
        let {
          product
        } = await this.$store.dispatch("get", {
          api: `products/${this.id}`,
          payload: {}
        })
        this.product = product
      } catch (e){
        this.$notify({
          group: "top-center",
          type: "error",
          title: e.message
        })
      } finally {
        this.isLoading = false
      }
    },
  }
}
</script>

<style>

</style>