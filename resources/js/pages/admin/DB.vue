<template>
  <b-container
    fluid
    class="py-4"
  >
    <b-form>
      <b-row>
        <b-col>
          <datetime
            v-model="sdate"
            placeholder="시작일"
            input-class="form-control"
          />
        </b-col>
        -
        <b-col>
          <datetime
            v-model="edate"
            placeholder="끝난일"
            input-class="form-control"
          />
        </b-col>
      </b-row>
    </b-form>
    <div>
      <b-table-simple>
        <b-thead>
          <tr>
            <th>id</th>
            <th>id</th>
            <th>날짜</th>
          </tr>
        </b-thead>
      </b-table-simple>
    </div>
  </b-container>
</template>

<script>
import { Datetime } from "vue-datetime"
import "vue-datetime/dist/vue-datetime.css"
export default {
  components: {
    Datetime
  },
  data(){
    return {
      sdate: null,
    }
  }
}
</script>

<style>

</style>