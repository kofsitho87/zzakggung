<template>
  <b-container v-if="user">
    <b-card
      tag="article"
      class="mb-5"
    >
      <b-form
        @submit="updateUser"
      >
        <b-form-group
          label="거래처이름"
          label-for="name"
          description="거래처이름"
        >
          <b-form-input
            id="name"
            v-model="user.name"
            type="text"
            required
          />
        </b-form-group>
        <b-form-group
          label="거래처아이디"
          label-for="email"
          description="거래처아이디"
        >
          <b-form-input
            id="email"
            v-model="user.email"
            type="text"
            required
          />
        </b-form-group>
        <b-form-group
          label="거래처타입"
          label-for="shop_type_id"
          description="거래처타입"
        >
          <b-form-select
            id="shop_type_id"
            v-model="user.shop_type_id"
            :options="shopTypes"
            required
          />
        </b-form-group>
      </b-form>

      <template v-slot:header>
        <strong>{{ user.name }}</strong> 거래처
      </template>
      <template v-slot:footer>
        <div class="float-right">
          <b-button
            variant="danger"
            @click="deleteUser"
            :disabled="isLoading"
          >
            삭제
          </b-button>
          <b-button
            variant="info"
            @click="updateUser"
            :disabled="isLoading"
          >
            업데이트
          </b-button>
        </div>
      </template>
    </b-card>
  </b-container>
</template>

<script>
export default {
  data(){
    return {
      isLoading: false,
      userId: this.$route.params.id,
      user: null
    }
  },
  computed: {
    shopTypes(){
      let shopTypes = this.$store.getters.shopTypes
      return [{value: null, text: "거래처타입"}, ...shopTypes.map(row => {
        return {
          value: row.id,
          text: row.type
        }
      })]
    }
  },
  mounted(){
    this.getUser()
  },
  methods: {
    async getUser(){
      this.isLoading = true
      try {
        let {
          user
        } = await this.$store.dispatch("get", {
          api: `user/${this.userId}`,
          payload: {}
        })
        this.user = user
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
    async updateUser(){
      this.isLoading = true
      try {
        await this.$store.dispatch("put", {
          api: `user/${this.userId}`,
          payload: this.user
        })
        this.$notify({
          group: "top-center",
          type: "success",
          title: "유저 업데이트 성공"
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
    },
    async deleteUser(){
      this.isLoading = true
      try {
        await this.$store.dispatch("delete", {
          api: `user/${this.userId}`
        })
        this.$notify({
          group: "top-center",
          type: "success",
          title: "유저 삭제 성공"
        })
        this.$router.go(-1)
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
  }
}
</script>

<style>

</style>