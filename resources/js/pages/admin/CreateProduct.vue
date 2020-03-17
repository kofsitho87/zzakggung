<template>
  <b-container>
    <b-card header="새상품등록">
      <b-form>
        <b-form-group
          label="모델번호"
          label-cols-sm="2"
          label-cols-lg="2"
        >
          <b-form-input
            v-model="$v.product.model_id.$model"
            :state="$v.product.model_id.$dirty ? !$v.product.model_id.$error : null"
            placeholder="모델번호"
          />
        </b-form-group>
        <b-form-group
          label="상품이름"
          label-cols-sm="2"
          label-cols-lg="2"
        >
          <b-form-input
            v-model="$v.product.name.$model"
            :state="$v.product.name.$dirty ? !$v.product.name.$error : null"
            placeholder="상품이름"
          />
        </b-form-group>
        <b-form-group
          v-for="item in product.prices"
          :key="item.id"
          :label="`거래처 - ${item.shop_type.type}`"
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
            to="/admin/products"
            size="sm"
          >
            목록
          </b-button>
          <b-button
            @click="addAction"
            variant="primary"
            size="sm"
            :disabled="isLoading"
          >
            생성
          </b-button>
        </div>
      </template>
    </b-card>
  </b-container>
</template>

<script>
import { validationMixin } from "vuelidate"
import { required, minLength } from "vuelidate/lib/validators"
export default {
  data(){
    let shopTypes = [...this.$store.getters.shopTypes].map(row => {
      return {
        text: row.type,
        value: row.id
      }
    })

    return {
      isLoading: false,
      product: {
        model_id: null,
        name: null,
        prices: shopTypes.map(row => {
          let data = {}
          data.price = 0
          data.shop_type = {
            type: row.text,
            id: row.value
          }
          return data
        })
      }
    }
  },
  validations: {
    product: {
      model_id: {
        required
      },
      name: {
        required
      }
    }
  },
  methods: {
    async addAction(){
      this.$v.product.$touch()
      if (this.$v.product.$anyError) {
        return
      }
      console.log(this.product)

      this.isLoading = true
      try {
        let {
          product
        } = await this.$store.dispatch("post", {
          api: "products",
          payload: this.product
        })
        
        this.$notify({
          group: "top-center",
          type: "success",
          title: "생성 성공"
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