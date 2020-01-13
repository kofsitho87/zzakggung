<template>
  <div class="pb-4">
    <b-card
      title="주문내역 관리"
      tag="article"
      class="mb-5"
    >
      <b-form @submit.prevent="searchAction">
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="정렬"
        >
          <b-form-select
            v-model="form.order_by"
          >
            <b-form-select-option :value="0">
              최신순
            </b-form-select-option>
            <b-form-select-option :value="1">
              오래된순
            </b-form-select-option>
          </b-form-select>
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="주문상태"
        >
          <b-form-select
            v-model="form.delivery_status"
            :options="order_status"
          />
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="기간검색"
        >
          <input
            type="date"
            v-model="form.sdate"
          > - <input
            type="date"
            v-model="form.edate"
          >
          <b-button
            variant="info"
            size="sm"
            @click="setFormDate('today')"
          >
            오늘
          </b-button>
          <b-button
            variant="info"
            size="sm"
            @click="setFormDate('yesterday')"
          >
            어제
          </b-button>
          <b-button
            variant="info"
            size="sm"
            @click="setFormDate('week')"
          >
            이번주
          </b-button>
          <b-button
            variant="info"
            size="sm"
            @click="setFormDate('month')"
          >
            이번달
          </b-button>
          <b-button
            variant="info"
            size="sm"
            @click="setFormDate('all')"
          >
            전체
          </b-button>
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="검색어"
        >
          <b-row>
            <b-col
              sm="4"
              md="2"
              lg="2"
            >
              <b-form-select
                v-model="form.keyword_option"
              >
                <b-form-select-option :value="0">
                  전체
                </b-form-select-option>
                <b-form-select-option :value="1">
                  받는분
                </b-form-select-option>
                <b-form-select-option :value="2">
                  받는분의 전화
                </b-form-select-option>
                <b-form-select-option :value="3">
                  운송장번호
                </b-form-select-option>
                <b-form-select-option :value="4">
                  주문번호
                </b-form-select-option>
                <b-form-select-option :value="5">
                  모델번호
                </b-form-select-option>
                <b-form-select-option :value="6">
                  거래처
                </b-form-select-option>
              </b-form-select>
            </b-col>
            <b-col>
              <b-form-input
                placeholder="받는사람 여러명 검색시 (,)로 구별해주세요"
                v-model="form.keyword"
              />
            </b-col>
          </b-row>
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="페이지검색갯수"
        >
          <b-form-select
            v-model="form.count"
          >
            <b-form-select-option :value="0">
              15
            </b-form-select-option>
            <b-form-select-option :value="1">
              30
            </b-form-select-option>
            <b-form-select-option :value="2">
              50
            </b-form-select-option>
            <b-form-select-option :value="3">
              100
            </b-form-select-option>
            <b-form-select-option :value="4">
              200
            </b-form-select-option>
            <b-form-select-option :value="5">
              500
            </b-form-select-option>
            <b-form-select-option :value="6">
              1000
            </b-form-select-option>
          </b-form-select>
        </b-form-group>
        <div class="float-right">
          <b-button
            size="sm"
            variant="primary"
            type="submit"
          >
            검색
          </b-button>
          <b-button
            size="sm"
            variant="success"
            @click="excelDownload"
          >
            엑셀다운로드
          </b-button>
        </div>
      </b-form>
    </b-card>
    <div>
      <b-button
        variant="outline-success"
        size="sm"
        :disabled="selectedRows.length < 1"
        v-b-modal.order_status_modal
      >
        주문상태일괄변경
      </b-button>
      <b-button
        variant="outline-secondary"
        size="sm"
        :disabled="selectedRows.length < 1"
        v-b-modal.order_comment_modal
      >
        참고사항일괄변경
      </b-button>
      <b-button
        variant="outline-danger"
        size="sm"
        :disabled="selectedRows.length < 1"
        @click="saveOrderDeletes"
      >
        주문내역일괄삭제
      </b-button>
    </div>
    <b-table
      ref="orders_table"
      class="orders_table"
      caption-top
      bordered
      hover
      :sticky-header="false"
      :fields="fields"
      :items="orders"
      :busy="isLoading"
      :selectable="true"
      select-mode="multi"
      :foot-clone="false"
      @row-selected="onRowSelected"
    >
      <template v-slot:cell(index)="data">
        {{ (currentPage-1) * perPage + (data.index + 1) }}
        <b-button
          size="sm"
          variant="info"
          @click="showChangeReceiverModal(data.index)"
        >
          고객정보수정
        </b-button>
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
          {{ row.detailsShowing ? 'hide' : 'show' }}
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

      <template v-slot:thead-top>
        <b-tr>
          <b-th
            colspan="2"
            variant="primary"
          >
            총합계액
          </b-th>
          <b-th
            variant="primary"
            colspan="14"
          >
            {{ $options.filters.comma(totalPrice) }}원
          </b-th>
        </b-tr>
      </template>
      <template v-slot:tfoot>
        <b-tr>
          <b-th colspan="2">
            title
          </b-th>
          <b-th
            colspan="14"
            variant="secondary"
          >
            Type 1
          </b-th>
        </b-tr>
      </template>

      <template v-slot:table-busy>
        <div class="text-center text-danger my-2">
          <b-spinner class="align-middle" />
          <strong>Loading...</strong>
        </div>
      </template>
      <template v-slot:table-caption>
        검색결과 총 {{ totalCount }}건
      </template>
    </b-table>
    <div>
      <b-pagination-nav
        v-model="page"
        :link-gen="linkGen"
        :number-of-pages="totalPage"
        use-router
      />
    </div>

    <b-modal
      id="order_status_modal"
      ref="order_status_modal"
      title="주문상태일괄변경"
    >
      <div>
        <b-form-select
          v-model="form.order_status"
          :options="order_status"
        />
      </div>
      <template v-slot:modal-footer>
        <b-button
          variant="success"
          :disabled="form.order_status == 0"
          @click="saveOrderStatus"
        >
          저장
        </b-button>
      </template>
    </b-modal>
    <b-modal
      id="order_comment_modal"
      ref="order_comment_modal"
      title="참고사항일괄변경"
    >
      <div>
        <b-form-input
          v-model="form.comment"
          placeholder="참고사항"
        />
      </div>
      <template v-slot:modal-footer>
        <b-button
          variant="success"
          @click="saveOrderComment"
        >
          저장
        </b-button>
      </template>
    </b-modal>
    <b-modal
      id="order_receiver_change"
      ref="order_receiver_change"
      title="고객정보수정"
      v-if="selectedReceiverData"
    >
      <b-form-group>
        <b-form-input
          placeholder="수령자"
          v-model="selectedReceiverData.receiver"
        />
      </b-form-group>
      <b-form-group>
        <b-form-input
          placeholder="주소"
          v-model="selectedReceiverData.address"
        />
      </b-form-group>
      <b-form-group>
        <b-form-input
          placeholder="전화번호1"
          v-model="selectedReceiverData.phone_1"
        />
      </b-form-group>
      <b-form-group>
        <b-form-input
          placeholder="전화번호2"
          v-model="selectedReceiverData.phone_2"
        />
      </b-form-group>
      <b-form-group>
        <b-form-input
          placeholder="송장번호"
          v-model="selectedReceiverData.delivery_code"
        />
      </b-form-group>
      <template v-slot:modal-footer>
        <b-button
          variant="success"
          @click="saveOrderReceiver"
        >
          저장
        </b-button>
      </template>
    </b-modal>
  </div>
</template>

<script>
import moment from "moment"
export default {
  data(){
    return {
      form: {
        order_by: 0,
        delivery_status: 0,
        sdate: null,
        edate: null,
        keyword_option: 0,
        keyword: null,
        count: 0,
        order_status: 0,
        comment: null
      },
      page: 1,
      totalPage: 1,
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
        }
      ],
      isLoading: false,
      orders: [],
      totalCount: 0,
      totalPrice: 0,
      selectedRows: [],
      order_status: [],
      selectedReceiverData: null
    }
  },
  watch: {
    "$route.query.page"(page) {
      this.page = page
      this.getOrders()
    }
  },
  mounted(){
    this.page = this.$route.query.page || 1
    this.getOrders()
  },
  methods: {
    async getOrders(){
      this.isLoading = true
      try {
        let {orders, total_price, order_status} = await this.$store.dispatch("get", {
          api: "orders",
          payload: {
            page: this.page,
            order_by: this.form.order_by,
            delivery_status: this.form.delivery_status,
            sdate: this.form.sdate,
            edate: this.form.edate,
            count: this.form.count,
            keyword_option: this.form.keyword_option,
            keyword: this.form.keyword
          }
        })
        this.orders = orders.data.map((item, idx) => {
          item.total_price = {
            price: item.product_price * item.qty + item.delivery_price - item.minus_price,
            minus: item.minus_price
          }
          return item
        })
        this.totalCount = orders.total
        this.totalPage = orders.last_page
        this.currentPage = orders.current_page
        this.perPage = orders.per_page
        this.totalPrice = total_price
        let orderStatus = Object.keys(order_status).map(key => {
          return {text: order_status[key], value:key}
        })
        this.order_status = [{text: "전체", value:0}, ...orderStatus]
      } catch (e){
        console.log(e)
      } finally {
        this.isLoading = false
      }
    },
    searchAction(){
      this.getOrders()
    },
    setFormDate(duration){
      switch(duration){
      case "today":
        var ymd = moment().format("YYYY-MM-DD")
        this.form.sdate = ymd
        this.form.edate = ymd
        break
      case "yesterday":
        var ymd = moment().subtract(1, "days").format("YYYY-MM-DD")
        this.form.sdate = ymd
        this.form.edate = ymd
        break
      case "week":
        let sweek = moment().startOf("week")
        this.form.sdate = sweek.format("YYYY-MM-DD")
        this.form.edate = moment().format("YYYY-MM-DD")
        break
      case "month":
        let smon = moment().startOf("month")
        this.form.sdate = smon.format("YYYY-MM-DD")
        this.form.edate = moment().format("YYYY-MM-DD")
        break
      case "all":
        this.form.sdate = null
        this.form.edate = null
        break
      }
      
    },
    linkGen(pageNum) {
      return `?page=${pageNum}`
    },
    onRowSelected(rows){
      this.selectedRows = rows
    },
    excelDownload(){
      let payload = {
        page: this.page,
        order_by: this.form.order_by,
        delivery_status: this.form.delivery_status,
        sdate: this.form.sdate,
        edate: this.form.edate,
        count: this.form.count,
        keyword_option: this.form.keyword_option,
        keyword: this.form.keyword
      }

      let url = "/admin/orders/export?"
      for(let key in payload) {
        let v = payload[key]
        if(v){
          url += `${key}=${payload[key]}&`
        }
      }
      console.log(url)
      window.open(url, "_blank")
    },
    async saveOrderStatus(){
      console.log("saveOrderStatus")
      try {
        await this.$store.dispatch("put", {
          api: "orders",
          payload: {
            delivery_status: this.form.order_status,
            orders: this.selectedRows
          }
        })
        this.$notify({
          group: "top-center",
          type: "success",
          title: "주문상태 업데이트 성공"
        })
        this.$refs.order_status_modal.hide()

        this.orders = this.orders.map(row => {
          if( this.selectedRows.find(item => item.id == row.id) ){
            let orderStatus = this.order_status.find(status => status.value == this.form.order_status)
            row.status = {
              id: orderStatus.value,
              name: orderStatus.text
            }
          }
          return row
        })
        this.form.order_status = 0
      } catch (e){
        this.$notify({
          group: "top-center",
          type: "error",
          title: e.message
        })
      } finally {
        //
      }
    },
    async saveOrderComment(){
      console.log("saveOrderComment", this.form.comment)

      try {
        await this.$store.dispatch("put", {
          api: "orders",
          payload: {
            comment: this.form.comment,
            orders: this.selectedRows
          }
        })
        this.$notify({
          group: "top-center",
          type: "success",
          title: "참고상태 업데이트 성공"
        })
        this.$refs.order_comment_modal.hide()

        this.orders = this.orders.map(row => {
          if( this.selectedRows.find(item => item.id == row.id) ){
            row.comment = this.form.comment
          }
          return row
        })
        this.form.comment = null
      } catch (e){
        this.$notify({
          group: "top-center",
          type: "error",
          title: e.message
        })
      } finally {
        //
      }
    },
    async saveOrderDeletes(){
      console.log("saveOrderDelete")
      if( confirm("선택한 주문내역을 정말 삭제하시겠습니까?") ){
        try {
          await this.$store.dispatch("delete", {
            api: "orders",
            payload: {
              orders: this.selectedRows
            }
          })
          this.$notify({
            group: "top-center",
            type: "success",
            title: "주문내역 삭제 성공"
          })

          this.orders = this.orders.filter(row => {
            if( this.selectedRows.find(item => item.id == row.id) ){
              return false
            }
            return true
          })
        } catch (e){
          this.$notify({
            group: "top-center",
            type: "error",
            title: e.message
          })
        } finally {
          //
        }
      }
    },
    showChangeReceiverModal(orderIndex){
      console.log(orderIndex)
      let currentOrder = this.orders[orderIndex]
      this.selectedReceiverData = Object.assign({}, currentOrder)
      this.$nextTick(() => {
        this.$refs.order_receiver_change.show()
      }) 
    },
    async saveOrderReceiver(){
      if( this.selectedReceiverData ){
        try {
          await this.$store.dispatch("put", {
            api: `orders/${this.selectedReceiverData.id}/receiver`,
            payload: this.selectedReceiverData
          })
          this.$notify({
            group: "top-center",
            type: "success",
            title: "주문자 정보 업데이트 성공"
          })
          this.$refs.order_receiver_change.hide()

          let orderIndex = this.orders.findIndex(order => order.id == this.selectedReceiverData.id)
          if(orderIndex > -1){
            this.orders[orderIndex] = this.selectedReceiverData
          }
          this.selectedReceiverData = null
          this.$refs.orders_table.refresh()

        } catch (e){
          this.$notify({
            group: "top-center",
            type: "error",
            title: e.message
          })
        } finally {
        //
        }
      }
    }
  }
}
</script>

<style>
table.orders_table th, table.orders_table td {
  font-size: 0.7rem;
}
</style>