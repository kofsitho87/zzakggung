<template>
  <b-container>
    <b-card>
      <b-form @submit.prevent="searchAction">
        <b-form-group>
          <b-form-input
            v-model="keyword"
            placeholder="모델번호나 상품이름으로 검색가능합니다."
          />
        </b-form-group>
      </b-form>

      <b-table
        :items="items"
        :fields="fields"
        :busy="isLoading"
        bordered
        caption-top
      >
        <template v-slot:cell(id)="data">
          <router-link
            :to="`/admin/products/${data.value}`"
          >
            {{ data.value }}
          </router-link>
        </template>
        <template v-slot:cell(prices)="data">
          <b-list-group>
            <b-list-group-item
              v-for="row in data.value"
              :key="row.id"
            >
              거래처-{{ row.shop_type ? row.shop_type.type : '없음' }} 
              {{ $options.filters.comma(row.price) }}원
            </b-list-group-item>
          </b-list-group>
        </template>

        <template v-slot:table-caption>
          검색결과 총 {{ total }}건
        </template>
      
        <template v-slot:table-busy>
          <div class="text-center text-danger my-2">
            <b-spinner class="align-middle" />
            <strong>Loading...</strong>
          </div>
        </template>
      </b-table>

      <template v-slot:header>
        상품관리
        <div class="float-right">
          <b-button
            variant="primary"
            size="sm"
            :to="{name: 'AdminCreateProduct'}"
          >
            새상품등록
          </b-button>
          <a
            href="/admin/products/example"
            class="btn btn-success btn-sm"
          >상품등록샘플다운로드</a>
          <b-button
            variant="info"
            size="sm"
            @click="$refs.excel.$el.querySelector('input').click()"
            accept=".xlsx"
            :disabled="isLoading"
          >
            엑셀파일로 상품 대량 업로드
          </b-button>
          <b-form-file
            ref="excel"
            v-model="excel"
            class="d-none"
            @change="uploadExcelAction"
            :disabled="isLoading"
          />
        </div>
      </template>
      <template v-slot:footer>
        <!-- <b-pagination
          v-model="page"
          :total-rows="total"
          per-page="15"
          align="fill"
          size="sm"
          pills
          @change="changePage"
        /> -->
        <b-pagination-nav
          v-model="page"
          :link-gen="linkGen"
          :number-of-pages="totalPage"
          use-router
        />
      </template>
    </b-card>
  </b-container>
</template>

<script>
export default {
  data(){
    return {
      keyword: null,
      page: this.$route.query.page || 1,
      fields: [
        {
          key: "id",
          label: "상품번호"
        },
        {
          key: "model_id",
          label: "모델번호"
        },
        {
          key: "name",
          label: "상품이름"
        },
        {
          key: "prices",
          label: "가격"
        },
      ],
      items: [],
      total: 0,
      totalPage: 1,
      isLoading: false,
      excel: null
    }
  },
  watch: {
    "$route.query.page"(page) {
      this.page = page
      this.getProducts()
    }
  },
  mounted(){
    this.getProducts()
  },
  methods: {
    async searchAction(){
      this.page = 1
      this.getProducts()
    },
    async getProducts(){
      this.isLoading = true
      try {
        let {
          products
        } = await this.$store.dispatch("get", {
          api: "products",
          payload: {
            page: this.page,
            keyword: this.keyword
          }
        })
        let list = products.data.map(row => {
          row.prices = row.prices.filter(item => item.shop_type)
          return row
        })
        this.items = products.data
        this.total = products.total
        this.totalPage = products.last_page
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
    linkGen(pageNum) {
      return `?page=${pageNum}`
    },
    async uploadExcelAction(){
      let excel = event.currentTarget.files[0]
      if(!excel){
        return
      }
      this.isLoading = true
      
      try {
        var formData = new FormData()
        formData.append("excel", excel)
        await this.$store.dispatch("post", {
          api: "products/upload",
          payload: formData
        })

        this.searchAction()
        
        this.$notify({
          type: "success",
          group: "top-center",
          title: "상품이 등록 되었습니다."
        })

      } catch (e){
        this.$notify({
          type: "error",
          group: "top-center",
          title: e.message
        })
      } finally {
        this.isLoading = false
        this.excel = null
      }
    }
  }
}
</script>

<style>

</style>