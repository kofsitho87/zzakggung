<template>
  <div v-if="user">
    <b-card
      header="적립내역 추가"
      tag="article"
      class="mb-5"
    >
      <b-card-text>
        Some quick example text to build on the card title and make up the bulk of the card's content.
      </b-card-text>

      <template v-slot:footer>
        <b-button
          variant="info"
          class="float-right"
        >
          생성
        </b-button>
      </template>
    </b-card>

    <b-card
      tag="article"
      class="mb-5"
    >
      <template v-slot:header>
        <strong>{{ user.name }}</strong>님 거래내역관리
      </template>

      <table class="table table-bordered mb-3">
        <colgroup>
          <col width="15%">
          <col width="30%">
          <col width="15%">
          <col width="*">
        </colgroup>
        <tbody>
          <tr>
            <th colspan="2">
              총적립금
            </th>
            <td colspan="2">
              {{ $options.filters.comma(total_plus_price) }}원
            </td>
          </tr>
          <tr>
            <th>사용가능적립금</th>
            <td>
              {{ $options.filters.comma(total_availble_price) }}원
            </td>
            <th>사용된적립금</th>
            <td>
              {{ $options.filters.comma(total_minus_price) }}원
            </td>
          </tr>
        </tbody>
      </table>

      <b-table
        :items="read_trades"
        :fields="fields"
        :busy="isLoading"
        bordered
      >
        <template v-slot:cell(index)="data">
          {{ (currentPage-1) * perPage + (data.index + 1) }}
        </template>
        <template v-slot:cell(created_at)="data">
          {{ $options.filters.dateFormat(data.value) }}
        </template>
        <template v-slot:cell(plus_price)="data">
          {{ $options.filters.comma(data.value) }}원
        </template>
        <template v-slot:cell(minus_price)="data">
          {{ $options.filters.comma(data.value) }}원
        </template>
        <template v-slot:cell(change)="data">
          {{ $options.filters.comma(data.value) }}원
        </template>
        <template v-slot:table-busy>
          <div class="text-center text-danger my-2">
            <b-spinner class="align-middle" />
            <strong>Loading...</strong>
          </div>
        </template>
      </b-table>

      <template v-slot:footer>
        <div class="overflow-auto">
          <b-pagination-nav
            :link-gen="linkGen"
            :number-of-pages="totalPage"
            use-router
          />
        </div>
      </template>
    </b-card>
  </div>
</template>

<script>
export default {
  data(){
    return {
      fields: [
        {
          key: "index",
          label: "No"
        },
        {
          key: "content",
          label: "상세내용",
        },
        {
          key: "created_at",
          label: "일자",
        },
        {
          key: "plus_price",
          label: "적립(+)",
        },
        {
          key: "minus_price",
          label: "적립(-)",
        },
        {
          key: "change",
          label: "잔액",
        }
      ],
      isLoading: false,
      userId: this.$route.params.id,
      user: null,
      read_trades: [],
      total_plus_price: 0,
      total_availble_price: 0,
      total_minus_price: 0,
      totalPage: 0,
      currentPage: 0,
      perPage: 0
    }
  },
  watch: {
    "$route.query.page"(page) {
      this.getTrades(page)
    }
  },
  mounted(){
    this.getTrades(1)
  },
  methods: {
    async getTrades(page){
      this.isLoading = true
      try {
        let {
          user, trades, read_trades, total_plus_price, total_availble_price, total_minus_price
        } = await this.$store.dispatch("get", {
          api: `user/${this.userId}/trades?page=${page}`,
          payload: {}
        })
        this.user = user
        this.total_plus_price = total_plus_price
        this.total_availble_price = total_availble_price
        this.total_minus_price = total_minus_price
        this.read_trades = read_trades.data.map((row, idx) => {
          row.plus_price = row.is_plus ? row.price : 0
          row.minus_price = !row.is_plus ? row.price : 0
          row.change = trades[ (read_trades.current_page-1) * read_trades.per_page + idx ].change
          return row
        })
        this.currentPage = read_trades.current_page
        this.perPage = read_trades.per_page
        this.totalPage = read_trades.last_page
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

</style>