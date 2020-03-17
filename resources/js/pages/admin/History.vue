<template>
  <b-container>
    <b-card header="사이트 업데이트 내역관리">
      <b-form
        @submit.prevent="addHistoryAction"
        class="mb-5"
      >
        <b-form-group>
          <!-- <b-form-input
            v-model="form.title"
            placeholder="타이틀"
            :state="$v.form.title.$dirty ? !$v.form.title.$error : null"
          /> -->
          <b-textarea
            v-model="form.title"
            :state="$v.form.title.$dirty ? !$v.form.title.$error : null"
            placeholder="타이틀"
          />
        </b-form-group>
        <!-- <b-form-group>
          <b-textarea
            v-model="form.desc"
            :state="$v.form.desc.$dirty ? !$v.form.desc.$error : null"
            placeholder="내용"
          />
        </b-form-group> -->
        <div class="float-right">
          <b-button
            variant="primary"
            type="submit"
            :disabled="isLoading"
          >
            생성
          </b-button>
        </div>
        <div class="clearfix" />
      </b-form>

      <b-table
        ref="table"
        hover
        :items="items"
        :fields="fields"
      >
        <template v-slot:cell(index)="data">
          {{ (page-1) * perPage + (data.index + 1) }}
        </template>
        <template v-slot:cell(title)="data">
          <p
            class="white-wrap"
            v-html="data.value"
          />
        </template>
        <template v-slot:cell(status)="data">
          <b-select
            :value="data.value"
            :options="statusOptions"
            @change="updateStatus(data.item)"
          />
        </template>
        <template v-slot:cell(delete)="data">
          <b-button
            size="sm"
            variant="danger"
            @click="deleteAction(data.item)"
          >
            삭제
          </b-button>
        </template>
      </b-table>
    </b-card>
  </b-container>
</template>

<script>
import { validationMixin } from "vuelidate"
import { required, minLength } from "vuelidate/lib/validators"
export default {
  mixins: [validationMixin],
  data(){
    return {
      isLoading: false,
      form: {
        title: null,
        //desc: null
      },
      perPage: 100,
      page: 1,
      items: [],
      fields: [
        {
          key: "index",
          label: "No"
        },
        {
          key: "title",
          label: "제목"
        },
        {
          key: "status",
          label: "상태"
        },
        // {
        //   key: "desc",
        //   label: "내용"
        // },
        {
          key: "created_at",
          label: "생성시간"
        },
        {
          key: "delete",
          label: "삭제"
        }
      ]
    }
  },
  validations: {
    form: {
      title: {
        required
      },
      // desc: {
      //   required,
      // }
    }
  },
  computed: {
    statusOptions(){
      return [
        "등록",
        "진행",
        "완료"
      ]
    }
  },
  mounted(){
    this.getItems()
  },
  methods: {
    async deleteAction(item){
      console.log(item)
      this.isLoading = true

      try {
        await this.$store.dispatch("delete", {
          api: `history/${item.id}`,
          payload: {}
        })
        let index = this.items.findIndex(row => row.id == item.id)
        if(index > -1){
          this.items.splice(index, 1)
        }

      } catch(e) {
        this.$notify({
          type: "error",
          group: "top-center",
          title: e.message
        })
      } finally {
        this.isLoading = false
      }

    },
    async updateStatus(item){
      // console.log(item)
      // console.log()
      let status = event.target.value
      
      this.isLoading = true
      try {
        await this.$store.dispatch("put", {
          api: `history/${item.id}`,
          payload: {
            status
          }
        })
        this.$notify({
          type: "success",
          group: "top-center",
          title: "상태가 변경 되었습니다."
        })
        
        switch(status){
        case "등록":
          item._rowVariant = ""
          break
        case "진행":
          item._rowVariant = "primary"
          break
        case "완료":
          item._rowVariant = "success"
          break
        }
        
        this.$refs.table.refresh()
        
        
      } catch(e) {
        this.$notify({
          type: "error",
          group: "top-center",
          title: e.message
        })
      } finally {
        this.isLoading = false
      }
    },
    async getItems(){
      this.isLoading = true
      try {
        let {items} = await this.$store.dispatch("get", {
          api: "history",
          payload: {}
        })
        console.log(items)
        this.items = items.data.map(item => {
          switch(item.status){
          case "등록":
            item._rowVariant = ""
            break
          case "진행":
            item._rowVariant = "primary"
            break
          case "완료":
            item._rowVariant = "success"
            break
          }
          return item
        })
        
      } catch(e) {
        this.$notify({
          type: "error",
          group: "top-center",
          title: e.message
        })
      } finally {
        this.isLoading = false
      }
    },
    async addHistoryAction(){
      this.$v.form.$touch()
      if (this.$v.form.$anyError) {
        return
      }

      this.isLoading = true

      try {
        let {history} = await this.$store.dispatch("post", {
          api: "history",
          payload: this.form
        })
        //console.log(history)
        history._rowVariant = ""
        this.items = [history, ...this.items]
        this.form.title = null
        this.form.desc = null
      } catch(e) {
        this.$notify({
          type: "error",
          group: "top-center",
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