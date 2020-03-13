<template>
  <b-container
    fluid
    class="py-4"
  >
    <b-form @submit.prevent="searchAction">
      <b-form-group 
        description="주문DB 검색"
        label="주문내역 삭제를 위해 시작날짜와 끝날짜를 선택해주세요"
      >
        <b-row class="">
          <b-col>
            <datetime
              v-model="sdate"
              placeholder="시작일"
              input-class="form-control"
            />
          </b-col>
          -
          <b-col>
            <datetime
              v-model="edate"
              placeholder="끝난일"
              input-class="form-control"
            />
          </b-col>
          <b-button type="submit" variant="primary">검색</b-button>
        </b-row>
      </b-form-group>
    </b-form>
    <div>
      <div class="d-flex pb-3">
        <div class="flex-grow-1">
          전체 검색 갯수: <strong>{{ orders.total }}</strong>
        </div>
        <b-button 
          variant="danger" 
          size="sm" 
          class="align-self-right"
          :disabled="isLoading || orders.total < 1"
          @click="deleteAllAction"
        >DB 삭제</b-button>
      </div>
      <b-table-simple>
        <b-thead>
          <tr>
            <th>id</th>
            <th>수령자</th>
            <th>날짜</th>
          </tr>
        </b-thead>
        <tbody>
          <tr v-for="item in orders.data" :key="item.id">
            <td>{{ item.id }}</td>
            <td>{{ item.receiver }}</td>
            <td>{{ item.created_at | dateFormat }}</td>
          </tr>
        </tbody>
      </b-table-simple>
    </div>
  </b-container>
</template>

<script>
import { Datetime } from "vue-datetime"
import "vue-datetime/dist/vue-datetime.css"
import moment from 'moment'
export default {
  components: {
    Datetime
  },
  data(){
    return {
      isLoading:false,
      sdate: null,
      edate: null,
      orders: {
        total: 0,
        data: [],
        current_page: 1,
      }
    }
  },
  methods: {
    async searchAction(){
      let sm = moment(this.sdate)
      let em = moment(this.edate)
      
      if(!sm.isValid() || !em.isValid()){
        this.$notify({
          group: "top-center",
          type: "error",
          title: "시작일과 끝날짜를 선택해주세요"
        })
        return
      }
      let sdate = sm.format("YYYY-MM-DD")
      let edate = em.format("YYYY-MM-DD")

      this.isLoading = true
      try {
        let {orders} = await this.$store.dispatch("get", {
          api: "db/raw_orders",
          payload: {
            //page: this.page,
            //order_by: this.form.order_by,
            sdate,
            edate,
            page: 1,
            count: 100,
          }
        })
        this.orders = orders
        
      } catch (e){
        console.log(e)
      } finally {
        this.isLoading = false
      }
    },
    async deleteAllAction(){
      if( !confirm("검색한 내역을 정말 삭제 하시겠습니까?") ){
        return
      }

      let sm = moment(this.sdate)
      let em = moment(this.edate)
      
      if(!sm.isValid() || !em.isValid()){
        this.$notify({
          group: "top-center",
          type: "error",
          title: "시작일과 끝날짜를 선택해주세요"
        })
        return
      }
      let sdate = sm.format("YYYY-MM-DD")
      let edate = em.format("YYYY-MM-DD")
      
      this.isLoading = true
      try {
        await this.$store.dispatch("post", {
          api: "db/delete_all",
          payload: {
            sdate,
            edate,
          }
        })
        this.orders = {
          total: 0,
          data: [],
          current_page: 1,
        }
        this.sdate = null
        this.edate = null
      } catch (e){
        console.log(e)
      } finally {
        this.isLoading = false
      }
    },
  }
}
</script>

<style>

</style>