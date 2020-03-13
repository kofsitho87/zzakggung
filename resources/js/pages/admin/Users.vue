<template>
  <b-container>
    <div>
      <h6>거래처 생성/수정 및 거래처별 주문내역 관리</h6>
    </div>
    <b-table
      striped
      hover
      :fields="fields"
      :items="users"
      :busy="isLoading"
    >
      <template v-slot:cell(index)="data">
        {{ data.index + 1 }}
      </template>
      <template v-slot:cell(user)="data">
        <router-link :to="{name: 'AdminUser', params: {id: data.value.id}}">
          {{ data.value.email }}
        </router-link>
      </template>
      <template v-slot:cell(trade_link_id)="data">
        <router-link :to="{name: 'AdminUserTrades', params: {id: data.value}}">
          거래내역확인
        </router-link>
      </template>

      <template v-slot:table-busy>
        <div class="text-center text-danger my-2">
          <b-spinner class="align-middle" />
          <strong>Loading...</strong>
        </div>
      </template>
      <template v-slot:table-caption>
        거래처 생성/수정 및 거래처별 주문내역 관리
      </template>
    </b-table>
  </b-container>
</template>

<script>
export default {
  data(){
    return {
      isLoading: false,
      fields: [
        {
          key: "index",
          label: "순서"
        },
        {
          key: "user",
          label: "아이디",
        },
        {
          key: "name",
          label: "거래처이름",
        },
        {
          key: "shop_type_id",
          label: "거채처타입",
        },
        {
          key: "created_at",
          label: "생성일",
        },
        {
          key: "trade_link_id",
          label: "거래내역관리",
        }
      ],
      users:[]
    }
  },
  mounted(){
    this.getUsers()
  },
  methods: {
    async getUsers(){
      this.isLoading = true
      try {
        let {users} = await this.$store.dispatch("get", {
          api: "users",
          payload: {}
        })
        this.users = users.data.map((item, idx) => {
          item.trade_link_id = item.id
          item.user = {
            id: item.id,
            email: item.email
          }
          return item
        })
      } catch (e){
        console.log(e)
      } finally {
        this.isLoading = false
      }
    }
  }
}
</script>

<style>

</style>