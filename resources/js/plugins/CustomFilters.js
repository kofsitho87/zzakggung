import moment from "moment"
moment.locale("ko")

export default {
  install(Vue) {
    Vue.filter("nl2br", function(value, is_xhtml) {
      if (!value) return ""
      // Adjust comment to avoid issue on phpjs.org display
      var breakTag =
        is_xhtml || typeof is_xhtml === "undefined" ? "<br " + "/>" : "<br>"
      return (value + "").replace(
        /([^>\r\n]?)(\r\n|\n\r|\r|\n)/g,
        "$1" + breakTag + "$2"
      )
    })
    Vue.filter("comma", function(value) {
      if (!value) return 0
      return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
    })
    Vue.filter("siteFormat", function(value) {
      if (!value) return null
      if (/(http|https):\/\//gi.test(value)) {
        return value
      }
      return `http://${value}`
    })
    Vue.filter("dateFormat", function(value, format) {
      if (!value) return null
      if (!format) {
        format = "YYYY-MM-DD"
      }
      return moment(value).format(format)
    })
    Vue.filter("fromNow", function(value) {
      if (!value) return null
      return moment(value).fromNow()
    })
    Vue.filter("phoneNumberFormat", function(value) {
      const tmp = value.replace(/[^\d]/g, "")
      return tmp.slice(0, 3) + "-" + tmp.slice(3, 7) + "-" + tmp.slice(7)
    })
    Vue.filter("phoneNumberReset", function(value) {
      return value.replace(new RegExp("-", "g"), "")
    })
  }
}
