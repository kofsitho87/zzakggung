<template>
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
      {{ (page-1) * perPage + (data.index + 1) }}
      <b-button
        class="font-size-1"
        size="sm"
        variant="info"
        @click="showChangeReceiverModal(data.index)"
      >
        고객정보수정
      </b-button>
    </template>
    <template v-slot:cell(id)="data">
      <router-link :to="{name: 'AdminOrder', params: {id: data.value}}">
        {{ data.value }}
      </router-link>
    </template>
    <template v-slot:cell(user)="data">
      <router-link :to="{name: 'AdminUserTrades', params: {id: data.value.id}}">
        <b class="text-info">{{ data.value.name }}</b>
      </router-link>
    </template>
    <template v-slot:cell(total_price)="data">
      {{ $options.filters.comma(data.value.price) }}원
      <span v-if="data.value.minus">
        (차감금액: {{ $options.filters.comma(data.value.minus) }}원)
      </span>
    </template>
    <template v-slot:cell(delivery_provider)="data">
      {{ data.value.name }}
    </template>
    <template v-slot:cell(status)="data">
      {{ data.value.name }}
      <b-button
        v-if="data.value.id == 3"
        size="sm"
        variant="outline-success"
        class="font-size-0 p-0"
        @click="updateOrderStatus(data.item)"
      >
        발송완료
      </b-button>
    </template>
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
            <!-- <th>참고사항</th> -->
            <th>교환/반품메세지</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{ row.item.delivery_message }}</td>
            <!-- <td>{{ row.item.comment }}</td> -->
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
          <b-button
            type="button"
            variant="success"
            size="sm"
            @click="toggleSelectAll"
          >
            전체{{ selectAll ? '해제' : '선택' }}
          </b-button>
        </b-th>
        <b-th
          colspan="2"
          variant="primary"
        >
          총합계액
        </b-th>
        <b-th
          variant="primary"
          colspan="15"
        >
          {{ $options.filters.comma(totalPrice) }}원
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
</template>

<script>
export default {
  props: {
    fields: {
      type: Array,
      default(){
        return []
      }
    },
    orders: {
      type: Array,
      default(){
        return []
      }
    },
    totalCount: {
      type: Number,
      default(){
        return 0
      }
    },
    totalPrice: {
      type: Number,
      default(){
        return 0
      }
    },
    isLoading: {
      type: Boolean,
      default(){
        return false
      }
    }
  },
  methods: {
    onRowSelected(rows){
      this.$emit("onRowSelected", rows)
    }
  }
}
</script>

<style>

</style>