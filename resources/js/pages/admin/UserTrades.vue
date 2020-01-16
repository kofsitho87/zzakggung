<template>
  <div v-if="user">
    <b-card
      header="적립내역 추가"
      tag="article"
      class="mb-5"
    >
      <b-table-simple responsive>
        <b-thead head-variant="light">
          <b-tr>
            <b-th>증감여부</b-th>
            <b-th>적립금</b-th>
            <b-th>내용</b-th>
          </b-tr>
        </b-thead>
        <b-tbody>
          <b-tr>
            <b-td>
              <b-form-select
                v-model="form.is_plus"
              >
                <b-form-select-option :value="false">
                  (-)적립금차감
                </b-form-select-option>
                <b-form-select-option :value="true">
                  (+)적립금증액
                </b-form-select-option>
              </b-form-select>
            </b-td>
            <b-td>
              <b-form-input
                type="number"
                v-model="$v.form.price.$model"
                :state="$v.form.price.$dirty ? !$v.form.price.$error : null"
              />
            </b-td>
            <b-td>
              <b-form-input
                v-model="$v.form.content.$model"
                :state="$v.form.content.$dirty ? !$v.form.content.$error : null"
              />
            </b-td>
          </b-tr>
        </b-tbody>
      </b-table-simple>

      <template v-slot:footer>
        <b-button
          variant="info"
          class="float-right"
          @click="addTradeHistory"
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
              {{ $options.filters.comma(totalAvailblePrice) }}원
            </td>
            <th>사용된적립금</th>
            <td>
              {{ $options.filters.comma(total_minus_price) }}원
            </td>
          </tr>
        </tbody>
      </table>

      <div>
        <b-button
          size="sm"
          variant="danger"
          :disabled="selectedRows.length < 1"
          @click="deleteSelectedRows"
        >
          삭제
        </b-button>
      </div>
      <b-table
        ref="read_trades"
        :items="read_trades"
        :fields="fields"
        :busy="isLoading"
        bordered
        :selectable="true"
        select-mode="multi"
        @row-selected="onRowSelected"
      >
        <template v-slot:cell(index)="data">
          {{ (page-1) * perPage + (data.index + 1) }}
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
            v-model="page"
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
import { required, minLength } from "vuelidate/lib/validators"
export default {
  data(){
    return {
      selectedRows: [],
      form: {
        is_plus: false,
        price: null,
        content: null
      },
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
      perPage: 0,
      page: 1
    }
  },
  validations: {
    form: {
      price: {
        required
      },
      content: {
        required
      }
    }
  },
  computed: {
    totalAvailblePrice(){
      return this.total_plus_price - this.total_minus_price
    }
  },
  watch: {
    "$route.query.page"(page) {
      this.page = page
      this.getTrades()
    }
  },
  mounted(){
    this.getTrades()
  },
  methods: {
    async getTrades(){
      this.isLoading = true
      try {
        let {
          user, trades, read_trades, total_plus_price, total_availble_price, total_minus_price
        } = await this.$store.dispatch("get", {
          api: `users/${this.userId}/trades?page=${this.page}`,
          payload: {}
        })
        this.user = user
        //this.total_availble_price = total_availble_price
        this.total_plus_price = total_plus_price
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
    async addTradeHistory(){
      this.$v.form.$touch()
      if (this.$v.form.$anyError) {
        return
      }

      this.isLoading = true
      try {
        let { trade } = await this.$store.dispatch("post", {
          api: `users/${this.userId}/trades`,
          payload: this.form
        })

        trade.plus_price = trade.is_plus ? trade.price : 0
        trade.minus_price = !trade.is_plus ? trade.price : 0
        trade.change = (this.read_trades.length > 0 ? this.read_trades[0].change : 0) + ( trade.is_plus ? parseInt(trade.price) : -parseInt(trade.price) )
        //this.total_availble_price = trade.change
        if( trade.is_plus ){
          this.total_plus_price += trade.price
        }else {
          this.total_minus_price += trade.price
        }
        this.read_trades.unshift(trade)
        this.$refs.read_trades.refresh()
        
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
    onRowSelected(rows){
      this.selectedRows = rows
    },
    async deleteSelectedRows(){
      //this.selectedRows

      this.isLoading = true
      try {
        let { trade } = await this.$store.dispatch("delete", {
          api: `users/${this.userId}/trades`,
          payload: {
            trades: this.selectedRows.map(row => row.id)
          }
        })
        this.selectedRows.forEach(row => {
          //console.log(this)
          let index = this.read_trades.findIndex(item => item.id == row.id)
          
          if(index > -1){
            let trade = this.read_trades[index]
            
            if( trade.is_plus ){
              this.total_plus_price -= trade.price
            }else {
              this.total_minus_price -= trade.price
            }
            this.read_trades.splice(index, 1)
          }
        })
        this.$refs.read_trades.clearSelected()
        


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