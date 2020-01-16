<template>
  <div>
    <b-card
      header="주문내역상세"
      sub-title="주문번호: "
      header-tag="header"
    >
      <div v-if="order">
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="주문일"
        >
          {{ this.order.created_at }}
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="거래처"
        >
          {{ this.order.user.name }}
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="모델번호"
        >
          {{ this.order.product.model_id }}
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="제품명"
        >
          {{ this.order.product.name }}
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="수량"
        >
          {{ this.order.qty }}
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="배송비"
        >
          {{ this.order.delivery_price }}
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="수령자"
        >
          {{ this.order.receiver }}
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="우편번호"
        >
          {{ this.order.zipcode }}
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="주소"
        >
          {{ this.order.address }}
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="전화번호1"
        >
          {{ this.order.phone_1 }}
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="전화번호2"
        >
          {{ this.order.phone_2 }}
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="교환/반품 메세지"
        >
          {{ this.order.delivery_message }}
        </b-form-group>

        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="배송상태"
        >
          <b-form-select v-model="order.delivery_status">
            <b-form-select-option :value="0">
              선택
            </b-form-select-option>
            <b-form-select-option :value="1">
              입금대기
            </b-form-select-option>
            <b-form-select-option :value="2">
              배송준비중
            </b-form-select-option>
            <b-form-select-option :value="3">
              발송대기
            </b-form-select-option>
            <b-form-select-option :value="4">
              발송완료
            </b-form-select-option>
            <b-form-select-option :value="5">
              반품요청
            </b-form-select-option>
            <b-form-select-option :value="6">
              교환요청
            </b-form-select-option>
            <b-form-select-option :value="7">
              반품완료
            </b-form-select-option>
            <b-form-select-option :value="8">
              교환완료
            </b-form-select-option>
          </b-form-select>
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="차감금액"
        >
          <b-form-input
            placeholder="차감금액"
            v-model="order.minus_price"
          />
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="배송사"
        >
          <b-form-select
            v-model="order.delivery_provider"
            :options="deliveryProviders"
          />
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="송장번호"
        >
          <b-form-input
            placeholder="송장번호"
            v-model="order.delivery_code"
          />
        </b-form-group>
        <b-form-group
          label-cols-sm="2"
          label-cols-lg="2"
          label="참고사항"
        >
          <b-form-textarea
            rows="6"
            placeholder="참고사항"
            v-model="order.comment"
          />
        </b-form-group>
      </div>

      <template v-slot:footer>
        <b-button
          :disabled="isLoading"
          class="float-right"
          variant="primary"
          @click="saveOrder"
        >
          저장
        </b-button>
      </template>
    </b-card>
  </div>
</template>

<script>
export default {
  data(){
    return {
      isLoading: false,
      form: {
        delivery_status: 0
      },
      id: this.$route.params.id,
      order: null
    }
  },
  computed: {
    deliveryProviders(){
      return [{text: "선택", value: null}, ...this.$store.getters.deliveryProviders]
    }
  },
  mounted(){
    this.getOrder()
  },
  methods: {
    async getOrder(){
      this.isLoading = true
      try {
        let {order} = await this.$store.dispatch("get", {
          api: `orders/${this.id}`,
          payload: {}
        })
        this.order = order
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
    async saveOrder(){
      this.isLoading = true
      try {
        let {order} = await this.$store.dispatch("put", {
          api: `orders/${this.id}`,
          payload: this.order
        })
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

<style>

</style>