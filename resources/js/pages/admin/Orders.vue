<template>
  <div class="pb-4">
    <b-table
      class="orders_table"
      caption-top
      bordered
      hover
      :sticky-header="false"
      :fields="fields"
      :items="orders"
      :busy="isLoading"
    >
      <template v-slot:cell(index)="data">
        {{ (currentPage-1) * perPage + (data.index + 1) }}
      </template>
      <template v-slot:cell(user)="data">
        <router-link :to="{name: 'AdminUser', params: {id: data.value.id}}">
          <b class="text-info">{{ data.value.email }}</b>
        </router-link>
      </template>
      <template v-slot:cell(total_price)="data">
        {{ $options.filters.comma(data.value.price) }}원
        <span v-if="data.value.minus">
          (차감금액: {{ $options.filters.comma(data.value.minus) }}원)
        </span>
      </template>
      <!-- <template v-slot:cell(product)="row">
        <b-button
          size="sm"
          @click="row.toggleDetails"
        >
          {{ row.detailsShowing ? 'Hide' : 'Show' }} Details
        </b-button>
      </template> -->
      <template v-slot:cell(delievery)="row">
        <b-button
          size="sm"
          @click="row.toggleDetails"
        >
          {{ row.detailsShowing ? 'Hide' : 'Show' }} Details
        </b-button>
      </template>
      <template v-slot:row-details="row">
        <table class="table">
          <thead>
            <tr>
              <th>배송메세지</th>
              <th>송장번호</th>
              <th>참고사항</th>
              <th>교환/반품메세지</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ row.item.delivery_message }}</td>
              <td>{{ row.item.delivery_code }}</td>
              <td>{{ row.item.comment }}</td>
              <td>{{ row.item.message ? row.item.message.content : null }}</td>
            </tr>
          </tbody>
        </table>
      </template>

      <template v-slot:table-busy>
        <div class="text-center text-danger my-2">
          <b-spinner class="align-middle" />
          <strong>Loading...</strong>
        </div>
      </template>
      <template v-slot:table-caption>
        주문내역 관리
      </template>
    </b-table>
    <div>
      <b-pagination-nav
        :link-gen="linkGen"
        :number-of-pages="totalPage"
        use-router
      />
    </div>
    <b-button
      variant="info"
      class="float-right"
    >
      생성
    </b-button>
  </div>
</template>

<script>
export default {
  data(){
    return {
      totalPage: 0,
      currentPage: 0,
      perPage: 0,
      fields: [
        {
          key: "index",
          label: "No"
        },
        {
          key: "id",
          label: "주문번호",
        },
        {
          key: "user",
          label: "거래처",
        },
        {
          key: "created_at",
          label: "주문일",
        },
        {
          key: "total_price",
          label: "가격합계",
        },
        {
          key: "product.model_id",
          label: "모델번호",
        },
        {
          key: "product.name",
          label: "제품명",
        },
        {
          key: "option",
          label: "옵션",
        },
        {
          key: "product_price",
          label: "가격",
        },
        {
          key: "qty",
          label: "수량",
        },
        {
          key: "delivery_price",
          label: "배송비",
        },
        {
          key: "receiver",
          label: "수령자",
        },
        {
          key: "phone_1",
          label: "전화번호1",
        },
        {
          key: "phone_2",
          label: "전화번호2",
        },
        {
          key: "status.name",
          label: "배송상태",
        },
        {
          key: "delievery",
          label: "배송기타데이터",
        },
      ],
      isLoading: false,
      orders: []
    }
  },
  watch: {
    "$route.query.page"(page) {
      this.getOrders(page)
    }
  },
  mounted(){
    this.getOrders(1)
  },
  methods: {
    async getOrders(page){
      this.isLoading = true
      try {
        let {orders} = await this.$store.dispatch("get", {
          api: `orders?page=${page}`,
          payload: {
            count: 3
          }
        })
        this.orders = orders.data.map((item, idx) => {
          item.total_price = {
            price: item.product_price * item.qty + item.delivery_price - item.minus_price,
            minus: item.minus_price
          }
          return item
        })
        this.totalPage = orders.last_page
        this.currentPage = orders.current_page
        this.perPage = orders.per_page
      } catch (e){
        console.log(e)
      } finally {
        this.isLoading = false
      }
    },
    linkGen(pageNum) {
      return `?page=${pageNum}`
    }
  }
}
</script>

<style>
table.orders_table th, table.orders_table td {
  font-size: 0.7rem;
}
</style>