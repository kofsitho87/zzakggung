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
        <b-form-group
          v-for="item in product.prices"
          :key="item.id"
          :label="`거래처 - ${item.shop_type ? item.shop_type.type : '없음'}`"
          label-cols-sm="2"
          label-cols-lg="2"
        >
          <b-form-input
            v-model="item.price"
            placeholder="가격"
          />
        </b-form-group>
      </b-form>
      <template v-slot:footer>
        <div class="float-right">
          <b-button
            @click="updateAction"
            variant="primary"
            size="sm"
            :disabled="isLoading"
          >
            업데이트
          </b-button>
          <b-button
            @click="deleteAction"
            variant="danger"
            size="sm"
            :disabled="isLoading"
          >
            삭제
          </b-button>
        </div>
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
        product.prices = product.prices.filter(row => row.shop_type)
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
    async updateAction(){
      this.isLoading = true
      try {
        let {
          product
        } = await this.$store.dispatch("put", {
          api: `products/${this.id}`,
          payload: this.product
        })
        this.$notify({
          group: "top-center",
          type: "success",
          title: "업데이트 성공"
        })
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
    async deleteAction(){
      this.isLoading = true
      try {
        let {
          product
        } = await this.$store.dispatch("delete", {
          api: `products/${this.id}`,
          payload: {}
        })
        this.$router.go(-1)
      } catch (e){
        this.$notify({
          group: "top-center",
          type: "error",
          title: e.message
        })
      } finally {
        this.isLoading = false
      }
    }
  }
}
</script>

<style>

</style>