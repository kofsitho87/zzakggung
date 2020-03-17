<template>
  <b-container>
    <b-card header="사이트 업데이트 내역관리">
      <b-form
        @submit.prevent="addHistoryAction"
        class="mb-5"
      >
        <b-form-group>
          <b-form-input
            v-model="form.title"
            placeholder="타이틀"
            :state="$v.form.title.$dirty ? !$v.form.title.$error : null"
          />
        </b-form-group>
        <b-form-group>
          <b-textarea
            v-model="form.desc"
            :state="$v.form.desc.$dirty ? !$v.form.desc.$error : null"
            placeholder="내용"
          />
        </b-form-group>
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
        striped
        hover
        :items="items"
        :fields="fields"
      >
        <template v-slot:cell(index)="data">
          {{ (page-1) * perPage + (data.index + 1) }}
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
        desc: null
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
          key: "desc",
          label: "내용"
        },
        {
          key: "created_at",
          label: "생성시간"
        }
      ]
    }
  },
  validations: {
    form: {
      title: {
        required
      },
      desc: {
        required,
      }
    }
  },
  mounted(){
    this.getItems()
  },
  methods: {
    async getItems(){
      this.isLoading = true
      try {
        let {items} = await this.$store.dispatch("get", {
          api: "history",
          payload: {}
        })
        console.log(items)
        this.items = items.data
        
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