<template>
  <div>
    <h5>공지사항</h5>
    <div>
      <vue-editor
        id="editor"
        use-custom-image-handler
        v-model="notice.content"
      />
      <div class="float-right">
        <b-button
          variant="primary"
          @click="updateNotice"
          :disabled="!notice.content || isLoading"
        >
          저장
        </b-button>
      </div>
    </div>
    <!-- <div class="mt-5">
      <b-card header="공지사항 목록">
        <b-table
          ref="table"
          caption-top
          bordered
          hover
          :sticky-header="false"
          :fields="fields"
          :items="items"
          :busy="isLoading"
          :selectable="false"
          :foot-clone="false"
        >
          <template v-slot:table-colgroup>
            <col
              :style="{ width: '60px' }"
            >
            <col
              :style="{ width: '*' }"
            >
            <col
              :style="{ width: '70px' }"
            >
          </template>
          <template v-slot:cell(index)="data">
            {{ (page-1) * perPage + (data.index + 1) }}
          </template>
          <template v-slot:cell(content)="data">
            <p
              class="pre-line"
              v-html="data.value"
            />
          </template>
          <template v-slot:cell(is_active)="data">
            <b-form-checkbox
              type="checkbox"
              v-model="data.item.is_active"
              @change="changeActive(data.item)"
            />
          </template>
        </b-table>
      </b-card>
    </div> -->
  </div>
</template>

<script>

import { VueEditor } from "vue2-editor"
export default {
  components: {
    VueEditor
  },
  data() {
    return {
      fields: [
        {
          key: "index",
          label: "No"
        },
        {
          key: "content",
          label: "내용"
        },
        {
          key: "is_active",
          label: "활성화"
        },
      ],
      page: 1,
      perPage: 15,
      items: [],
      isLoading: false,
      notice: {
        content: null
      }
    }
  },
  mounted() {
    this.getNotice()
  },
  methods: {
    async getNotice() {
      this.isLoading = true
      try {
        let {notice} = await this.$store.dispatch("get", {
          api: "notices/1",
          payload: {}
        })
        this.notice = notice
        //this.items = notices.data
        this.htmlForEditor = notice.content
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
    async saveNotice(){
      console.log(this.htmlForEditor)
      if(!this.htmlForEditor){
        return
      }
      
      this.isLoading = true
      try {
        let {notice} = await this.$store.dispatch("post", {
          api: "notices",
          payload: {
            content: this.htmlForEditor
          }
        })
        this.$notify({
          group: "top-center",
          type: "success",
          title: "공지사항 생성 성공"
        })
        this.htmlForEditor = null
        this.items.push(notice)
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
    async updateNotice(){
      this.isLoading = true
      try {
        let {notice} = await this.$store.dispatch("put", {
          api: "notices/1",
          payload: this.notice
        })
        this.$notify({
          group: "top-center",
          type: "success",
          title: "저장되었습니다."
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