<template>
  <vue-editor
    id="editor"
    use-custom-image-handler
    v-model="htmlForEditor"
  />
</template>

<script>
import { VueEditor } from "vue2-editor"
export default {
  components: {
    VueEditor
  },
  data(){
    return {
      htmlForEditor: ""
    }
  },
  mounted() {
    
    
    // $(this.$refs.summernote).summernote({
    //   height: 100
    // })
  },

  methods: {
    getVal() {
      //var data  = $(this.$refs.summernote).summernote("code")
      //return data
    },
    run(code, value) {
      if(value == undefined) {
        //$(this.$refs.summernote).summernote(code)
      }else {
        //$(this.$refs.summernote).summernote(code,value)
      }
    }
  }
}
</script>

<style>

</style>