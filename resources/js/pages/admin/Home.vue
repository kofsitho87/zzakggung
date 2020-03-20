<template>
  <b-container>
    <b-row>
      <b-col
        lg="6"
        md="12"
      >
        <b-card header="시간대별 주문량">
          <line-chart
            ref="topDate"
            :data="topDateData"
            :options="lineOptions"
          />
        </b-card>
      </b-col>
      <b-col
        lg="6"
        md="12"
      >
        <b-card header="주문이 가장 많은 도시">
          <bar-chart
            ref="topCity"
            :data="topCityData"
            :options="options"
          />
        </b-card>
      </b-col>
    </b-row>
    <div class="py-4" />
    <b-row>
      <b-col
        lg="6"
        md="12"
      >
        <b-card header="가장많이 팔린 상품">
          <bar-chart
            ref="topProduct"
            :data="topProductData"
            :options="options"
          />
        </b-card>
      </b-col>
      <b-col
        lg="6"
        md="12"
      >
        <b-card header="가장 주문이 많은 업체">
          <bar-chart
            ref="topProvider"
            :data="topProviderData"
            :options="options"
          />
        </b-card>
      </b-col>
    </b-row>
    <!-- <div class="py-4" />
    <b-row>
      <b-col>
        <b-card header="주문이 가장 많은 도시">
          <bar-chart
            ref="topCity"
            :data="topCityData"
            :options="options"
          />
        </b-card>
      </b-col>
    </b-row> -->
  </b-container>
</template>

<script>
import BarChart from "../../components/chart/BarChart.js"
import LineChart from "../../components/chart/LineChart.js"
export default {
  components: {
    BarChart,
    LineChart
  },
  data(){
    return {
      isLoading: false,
      topProductData: {
        labels: [],
        datasets: [
          {
            label: "가장많이 팔린 상품",
            backgroundColor: "#f87979",
            pointBackgroundColor: "white",
            borderWidth: 1,
            pointBorderColor: "#249EBF",
            data: []
          }
        ]
      },
      topProviderData: {
        labels: [],
        datasets: [
          {
            label: "가장 주문이 많은 업체",
            backgroundColor: "#f87979",
            pointBackgroundColor: "white",
            borderWidth: 1,
            pointBorderColor: "#249EBF",
            data: []
          }
        ]
      },
      topCityData: {
        labels: [],
        datasets: [
          {
            label: "주문이 가장 많은 도시",
            backgroundColor: "#f87979",
            pointBackgroundColor: "white",
            borderWidth: 1,
            pointBorderColor: "#249EBF",
            data: []
          }
        ]
      },
      topDateData: {
        labels: [],
        datasets: [
          {
            label: "시간대별 주문량",
            //backgroundColor: "#f87979",
            borderColor: "#f87979",
            pointBackgroundColor: "#f87979",
            borderWidth: 3,
            pointBorderColor: "#f87979",
            data: [],
            fill: false,
          }
        ]
      },
      lineOptions: {
        responsive: true,
        title: {
          display: false,
          text: "시간대별 주문량"
        },
        tooltips: {
          enabled: true,
          mode: "single",
          callbacks: {
            label: function(tooltipItems, data) {
              return tooltipItems.yLabel
            }
          }
        },
        scales: {
          yAxes: [
            {
              display: true,
              scaleLabel: {
                display: true,
                labelString: "주문량"
              }
            }
          ],
          xAxes: [
            {
              display: true,
              scaleLabel: {
                display: true,
                labelString: "해당 년월"
              }
            }
          ]
        }
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            },
            gridLines: {
              display: true
            }
          }],
          xAxes: [{
            ticks: {
              beginAtZero: true
            },
            gridLines: {
              display: false
            }
          }]
        },
        legend: {
          display: false
        },
        tooltips: {
          enabled: true,
          mode: "single",
          callbacks: {
            label: function(tooltipItems, data) {
              return tooltipItems.yLabel
            }
          }
        },
        responsive: true,
        maintainAspectRatio: false,
        height: 200
      }
    }
  },
  mounted(){
    this.getData()
  },
  methods: {
    async getData(){
      this.isLoading = true
      try {
        //시간대별 주문량
        var {items} = await this.$store.dispatch("get", {
          api: "statistics/top_order_by_date",
          payload: {}
        })
        this.topDateData.labels = items.map(row => {
          return row.date_ymd
        })
        this.topDateData.datasets[0].data = items.map(row => {
          return row.cnt
        })
        this.$refs.topDate.render()

        //주문이 가장 많은 도시
        var {groupCount} = await this.$store.dispatch("get", {
          api: "statistics/top_order_city",
          payload: {}
        })
        let groups = Object.keys(groupCount)
        var items = groups.map(row => {
          return {
            total: groupCount[row],
            title: row
          }
        })
        this.topCityData.labels = items.map(row => {
          return row.title
        })
        this.topCityData.datasets[0].data = items.map(row => {
          return row.total
        })
        this.$refs.topCity.render()




        var {items} = await this.$store.dispatch("get", {
          api: "statistics/top_products",
          payload: {}
        })
        this.topProductData.labels = items.map(row => {
          return row.product.name
        })
        this.topProductData.datasets[0].data = items.map(row => {
          return row.total
        })
        this.$refs.topProduct.render()


        var {items} = await this.$store.dispatch("get", {
          api: "statistics/top_order_by_provider",
          payload: {}
        })
        this.topProviderData.labels = items.map(row => {
          return row.user.name
        })
        this.topProviderData.datasets[0].data = items.map(row => {
          return row.total
        })
        this.$refs.topProvider.render()
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
