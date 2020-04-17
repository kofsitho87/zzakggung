<template>
  <div class="pb-4">
    <b-card
      header="주문내역 관리"
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
            variant="outline-primary"
            size="sm"
            @click="setFormDate('today')"
          >
            오늘
          </b-button>
          <b-button
            variant="outline-primary"
            size="sm"
            @click="setFormDate('yesterday')"
          >
            어제
          </b-button>
          <b-button
            variant="outline-primary"
            size="sm"
            @click="setFormDate('week')"
          >
            이번주
          </b-button>
          <b-button
            variant="outline-primary"
            size="sm"
            @click="setFormDate('month')"
          >
            이번달
          </b-button>
          <b-button
            variant="outline-primary"
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
          label="페이지검색개수"
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
            :disabled="form.sdate == null || form.edate == null"
          >
            엑셀다운로드
          </b-button>
          <b-button
            size="sm"
            variant="danger"
            @click="$refs.excel.$el.querySelector('input').click()"
          >
            엑셀업로드
          </b-button>
          <b-form-file
            ref="excel"
            class="d-none"
            accept=".xlsx"
            @change="excelUpload"
          />
        </div>
      </b-form>
    </b-card>
    <div>
      <b-button
        variant="outline-success"
        size="sm"
        :disabled="selectedRowsCount < 1"
        v-b-modal.order_status_modal
      >
        주문상태일괄변경
      </b-button>
      <b-button
        variant="outline-secondary"
        size="sm"
        :disabled="selectedRowsCount < 1"
        v-b-modal.order_comment_modal
      >
        참고사항일괄변경
      </b-button>
      <b-button
        variant="outline-danger"
        size="sm"
        :disabled="selectedRowsCount < 1"
        @click="saveOrderDeletes"
      >
        주문내역일괄삭제
      </b-button>
    </div>
    
    <OrderListSimpleGroup
      ref="orders_table"
      :items="orders"
      :fields="fields"
      :total-count="totalCount"
      :total-price="totalPrice"
      :is-loading="isLoading"
      :page="page"
      :per-page="perPage"
      :is-selected-all="isSelectedAll"
      @toggleSelectAll="toggleSelectAll"
      @onRowSelected="onRowSelected"
      @showChangeReceiverModal="showChangeReceiverModal"
      @updateOrderStatus="updateOrderStatus"
    />

    <div>
      <b-pagination-nav
        v-model="page"
        :link-gen="linkGen"
        :number-of-pages="totalPage"
        use-router
      />
    </div>

    <b-spinner
      variant="primary"
      label="Loading..."
      class="spinner"
      v-if="isLoading"
    />

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
      <div
        v-if="form.order_status == 7"
        class="mt-2"
      >
        <b-form-input
          type="number"
          v-model="form.refund"
          placeholder="환불금액"
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
      <b-form-group
        label-cols-sm="2"
        label-cols-lg="2"
        label="수령자"
      >
        <b-form-input
          placeholder="수령자"
          v-model="selectedReceiverData.receiver"
        />
      </b-form-group>
      <b-form-group
        label-cols-sm="2"
        label-cols-lg="2"
        label="주소"
      >
        <b-form-input
          placeholder="주소"
          v-model="selectedReceiverData.address"
        />
      </b-form-group>
      <b-form-group
        label-cols-sm="2"
        label-cols-lg="2"
        label="우편번호"
      >
        <b-form-input
          placeholder="우편번호"
          v-model="selectedReceiverData.zipcode"
        />
      </b-form-group>
      <b-form-group
        label-cols-sm="2"
        label-cols-lg="2"
        label="전화번호1"
      >
        <b-form-input
          placeholder="전화번호1"
          v-model="selectedReceiverData.phone_1"
        />
      </b-form-group>
      <b-form-group
        label-cols-sm="2"
        label-cols-lg="2"
        label="전화번호2"
      >
        <b-form-input
          placeholder="전화번호2"
          v-model="selectedReceiverData.phone_2"
        />
      </b-form-group>
      <b-form-group
        label-cols-sm="2"
        label-cols-lg="2"
        label="송장번호"
      >
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
import OrderListSimpleGroup from "../../components/OrderListSimpleGroup"
export default {
  components: {
    OrderListSimpleGroup
  },
  data(){
    let {query} = this.$route
    return {
      form: {
        order_by: query.order_by || 0,
        delivery_status: query.delivery_status || 0,
        sdate: query.sdate || null,
        edate: query.edate || null,
        keyword_option: query.keyword_option || 0,
        keyword: query.keyword || null,
        count: query.count || 0,
        order_status: 0,
        comment: null,
        refund: 0
      },
      page: 1,
      totalPage: 1,
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
          key: "delivery_provider",
          label: "배송사",
        },
        {
          key: "delivery_code",
          label: "송장번호",
        },
        {
          key: "status",
          label: "배송상태",
        },
        {
          key: "comment",
          label: "참고사항",
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
      selectedReceiverData: null,
      formDate: "all",
      isSelectedAll: false,
    }
  },
  watch: {
    "$route.query.page"(page) {
      this.page = page
      this.getOrders()
    }
  },
  computed: {
    selectedRowsCount(){
      return this.orders.filter(order => order.selected).length
    }
  },
  mounted(){
    let page = this.$route.query.page || 1
    this.page = parseInt(page)
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
          item.seleced = false
          return item
        })
        this.totalCount = orders.total
        this.totalPage = orders.last_page
        this.currentPage = orders.current_page
        this.perPage = orders.per_page
        this.totalPrice = total_price
        let orderStatus = Object.keys(order_status).map(key => {
          return {text: order_status[key], value: key}
        })
        this.order_status = [{text: "전체", value: 0}, ...orderStatus]


        this.$router.push({
          query: {
            sdate: this.form.sdate,
            edate: this.form.edate,
            keyword_option: this.form.keyword_option,
            keyword: this.form.keyword,
            count: this.form.count,
            order_by: this.form.order_by,
            delivery_status: this.form.delivery_status,
            page: this.page,
          }
        }).catch(err => {})
      
      } catch (e){
        console.log(e)
      } finally {
        this.isLoading = false
      }
    },
    searchAction(){
      //this.$router.replace("/admin/orders?page=1")
      this.page = 1
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
      //this.formDate = duration
    },
    linkGen(pageNum) {
      return `?page=${pageNum}`
    },
    onRowSelected(item){
      item.selected = !item.selected
      this.orders = this.orders.map(order => {
        if(order.id == item.id){
          return item
        }
        return order
      })
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
        let orders = this.orders.filter(row => row.selected)
        await this.$store.dispatch("put", {
          api: "orders",
          payload: {
            delivery_status: this.form.order_status,
            refund: this.form.refund,
            orders
          }
        })
        let orderStatus = this.order_status.find(row => row.value == this.form.order_status)
        console.log(orderStatus)
        this.orders = this.orders.map(row => {
          if(row.selected){
            row.status.name = orderStatus.text
            row.status.id = parseInt(orderStatus.value)
          }
          return row
        })
        
        this.$notify({
          group: "top-center",
          type: "success",
          title: "주문상태 업데이트 성공"
        })
        this.$refs.order_status_modal.hide()
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
            orders: this.orders.filter(order => order.selected)
          }
        })
        this.$notify({
          group: "top-center",
          type: "success",
          title: "참고상태 업데이트 성공"
        })
        this.$refs.order_comment_modal.hide()

        this.orders = this.orders.map(order => {
          if(order.selected){
            order.comment = this.form.comment
          }
          return order
        })
        // this.orders = this.orders.map(row => {
        //   if( this.selectedRows.find(item => item.id == row.id) ){
        //     row.comment = this.form.comment
        //   }
        //   return row
        // })
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
          let deleteOrders = this.orders.filter(order => order.selected)
          await this.$store.dispatch("post", {
            api: "orders",
            payload: {
              orders: deleteOrders
            }
          })
          this.$notify({
            group: "top-center",
            type: "success",
            title: "주문내역 삭제 성공"
          })

          this.orders = this.orders.filter(order => !order.selected)
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
    showChangeReceiverModal(order){
      this.selectedReceiverData = Object.assign({}, order)
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
            console.log(this.selectedReceiverData)
            let currentOrder = this.orders[orderIndex]
            currentOrder.receiver = this.selectedReceiverData.receiver
            currentOrder.phone_1 = this.selectedReceiverData.phone_1
            currentOrder.phone_2 = this.selectedReceiverData.phone_2
            currentOrder.zipcode = this.selectedReceiverData.zipcode
            currentOrder.address = this.selectedReceiverData.address
            currentOrder.delivery_code = this.selectedReceiverData.delivery_code
            this.orders[orderIndex] = currentOrder
            //this.orders[orderIndex] = this.selectedReceiverData
          }
          this.selectedReceiverData = null

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
    toggleSelectAll(){
      this.isSelectedAll = !this.isSelectedAll
      this.orders = this.orders.map(order => {
        order.selected = this.isSelectedAll
        return order
      })
      //this.selectAll ? this.$refs.orders_table.selectAllRows() : this.$refs.orders_table.clearSelected()
      // this.selectedRows = this.selectAll ? this.orders : []
      // this.$refs.orders_table.refresh()
    },
    async excelUpload(e){
      let file = e.target.files[0]
      console.log(file)
      
      this.isLoading = true
      try {
        var formData = new FormData()
        formData.append("excel", file)
        await this.$store.dispatch("post", {
          api: "orders/upload",
          payload: formData
        })
        this.$notify({
          group: "top-center",
          type: "success",
          title: "파일업로드 성공"
        })
        this.getOrders()

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
    async updateOrderStatus(order){
      const {minus_price, delivery_code, comment, delivery_provider} = order

      this.isLoading = true
      try {
        await this.$store.dispatch("put", {
          api: `orders/${order.id}`,
          payload: {
            delivery_status: 4, 
            minus_price, 
            delivery_provider: delivery_provider ? delivery_provider.id : null, 
            delivery_code, 
            comment
          }
        })
        order.status.id = 4
        order.status.name = "발송완료"
        this.$notify({
          group: "top-center",
          type: "success",
          title: "주문내역 업데이트 성공"
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
    }
  }
}
</script>

<style lang="scss">
.spinner {
  position: fixed;
  left: 50%;
  top:45%;
}
table.orders_table tr.selected {
  background-color: #dfdfdf;
}
table.orders_table th, table.orders_table td {
  font-size: 0.7rem;
}

#order_receiver_change {
  font-size:0.8rem;
}
</style>