<template>
  <b-container>
    <b-card header="거래처타입관리">
      <b-table-simple ref="table" responsive bordered>
        <b-thead head-variant="light">
          <b-tr>
            <b-th>거래처타입</b-th>
            <b-th>배송비</b-th>
            <b-th>초기배송상태</b-th>
            <b-th>삭제</b-th>
          </b-tr>
        </b-thead>
        <b-tbody>
          <b-tr v-for="item in shopTypes" :key="item.id">
            <b-td>
              <div v-if="item.id">
                {{ item.type }}
              </div>
              <b-form-input
                v-else
                v-model="item.type"
                placeholder="거래처타입(1글자)"
              />
            </b-td>
            <b-td>
              <!-- {{ $options.filters.comma(item.delivery_price) }}원 -->
              <b-form-input
                placeholder="배송비"
                type="number"
                v-model="item.delivery_price"
                @change="changeItem(item)"
              />
            </b-td>
            <b-td>
              <div v-if="item.id">
                {{ item.status.name }}
              </div>
              <b-form-select v-else v-model="item.delivery_status">
                <b-form-select-option :value="0"> 선택 </b-form-select-option>
                <b-form-select-option :value="1">
                  입금대기
                </b-form-select-option>
                <b-form-select-option :value="2">
                  배송준비중
                </b-form-select-option>
                <b-form-select-option :value="3">
                  발송대기
                </b-form-select-option>
                <b-form-select-option :value="4">
                  발송완료
                </b-form-select-option>
                <b-form-select-option :value="5">
                  반품요청
                </b-form-select-option>
                <b-form-select-option :value="6">
                  교환요청
                </b-form-select-option>
                <b-form-select-option :value="7">
                  반품완료
                </b-form-select-option>
                <b-form-select-option :value="8">
                  교환완료
                </b-form-select-option>
              </b-form-select>
            </b-td>
            <b-td>
              <b-button-group>
                <b-button
                  v-if="item.id == 0"
                  size="md"
                  variant="success"
                  @click="createItem(item)"
                >
                  생성
                </b-button>
                <b-button size="md" variant="danger" @click="deleteItem(item)">
                  삭제
                </b-button>
              </b-button-group>
            </b-td>
          </b-tr>
        </b-tbody>
        <b-tfoot>
          <b-tr>
            <b-td colspan="4">
              <b-button
                block
                variant="primary"
                @click="addItem"
                :disabled="!isEnableAddItem"
              >
                + 추가
              </b-button>
            </b-td>
          </b-tr>
        </b-tfoot>
      </b-table-simple>
    </b-card>
  </b-container>
</template>

<script>
export default {
  data() {
    //console.log( this.$store )

    return {
      isLoading: false,
      shopTypes: JSON.parse(JSON.stringify(this.$store.getters.shopTypes)),
    };
  },
  computed: {
    isEnableAddItem() {
      return this.shopTypes.filter((row) => row.id === 0).length < 1;
    },
    // shopTypes(){
    //   return this.$store.getters.shopTypes
    // }
  },
  mounted() {
    this.$store.dispatch("getShopTypes");
  },
  methods: {
    addItem() {
      const shopType = {
        id: 0,
        type: null,
        delivery_price: 0,
        delivery_status: 0,
        status: {
          id: 0,
          name: null,
          description: null,
        },
      };
      //this.$store.commit("CREATE_SHOP_TYPE", shopType)
      this.shopTypes = [...this.shopTypes, shopType];
    },
    async createItem(item) {
      //console.log(item)
      if (
        !item.type ||
        item.delivery_price.length < 1 ||
        !item.delivery_status
      ) {
        this.$notify({
          group: "top-center",
          type: "error",
          title: "값을 입력해주세요",
        });
        return;
      } else if (item.type.length != 1) {
        this.$notify({
          group: "top-center",
          type: "error",
          title: "거래처타입은 1글자만 입력해주세요",
        });
        return;
      } else if (item.delivery_price < 0) {
        this.$notify({
          group: "top-center",
          type: "error",
          title: "배송비는 양수로 입력해주세요",
        });
        return;
      }

      this.isLoading = true;
      try {
        let { shopType } = await this.$store.dispatch("post", {
          api: "shop_types",
          payload: item,
        });
        //console.log(shopType)

        this.$store.commit("CREATE_SHOP_TYPE", shopType);
        item.id = shopType.id;
        item.status.name = shopType.status.name;
        //this.shopTypes[this.shopTypes.length - 1] = shopType
        //this.$refs.table.refresh()

        this.$notify({
          group: "top-center",
          type: "success",
          title: "생성 성공",
        });
      } catch (e) {
        this.$notify({
          group: "top-center",
          type: "error",
          title: e.message,
        });
      } finally {
        this.isLoading = false;
      }
    },
    async deleteItem(item) {
      console.log(item);

      if (item.id) {
        this.isLoading = true;
        try {
          await this.$store.dispatch("delete", {
            api: `shop_types/${item.id}`,
            payload: {},
          });
          let index = this.shopTypes.findIndex((row) => row.id == item.id);
          if (index > -1) {
            this.shopTypes.splice(index, 1);
          }
          this.$store.commit("DELETE_SHOP_TYPE", item);
          this.$notify({
            group: "top-center",
            type: "success",
            title: "삭제 성공",
          });
        } catch (e) {
          this.$notify({
            group: "top-center",
            type: "error",
            title: e.message,
          });
        } finally {
          this.isLoading = false;
        }
      } else {
        this.shopTypes.splice(this.shopTypes.length - 1, 1);
      }
    },
    async changeItem(item) {
      if (!item.id) {
        return;
      }
      if (item.delivery_price.length === 0) {
        item.delivery_price = 0;
        this.$notify({
          group: "top-center",
          type: "error",
          title: "배송비를 입력해주세요",
        });
        return;
      } else if (item.delivery_price < 0) {
        item.delivery_price = 0;
        this.$notify({
          group: "top-center",
          type: "error",
          title: "배송비는 0이상 값을 입력해주세요",
        });
        return;
      }

      this.isLoading = true;
      try {
        await this.$store.dispatch("put", {
          api: `shop_types/${item.id}`,
          payload: {
            delivery_price: parseInt(item.delivery_price),
          },
        });

        item.delivery_price = parseInt(item.delivery_price);
        this.$store.commit("UPDATE_SHOP_TYPE", item);

        this.$notify({
          group: "top-center",
          type: "success",
          title: "업데이트 성공",
        });
      } catch (e) {
        this.$notify({
          group: "top-center",
          type: "error",
          title: e.message,
        });
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>

<style>
</style>