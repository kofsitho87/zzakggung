import { Pie, mixins } from "vue-chartjs"
//const { reactiveProp } = mixins

export default {
  extends: Pie,
  //mixins: [reactiveProp],
  props: ["data", "options"],
  methods: {
    render(){
      this.renderChart(this.data, this.options)
    }
  }
}