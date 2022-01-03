<template>
  <b-container>
    <b-card v-if="isShowAdd" header="배송사추가" class="mb-4">
      <b-form @submit.prevent="addProvider">
        <b-form-group label="배송사">
          <b-form-input placeholder="배송사명" v-model="provider" />
        </b-form-group>
        <div class="text-right">
          <b-button variant="primary" size="sm" type="submit"> 생성 </b-button>
          <b-button variant="danger" size="sm" @click="isShowAdd = false">
            닫기
          </b-button>
        </div>
      </b-form>
    </b-card>
    <b-card>
      <template v-slot:header>
        배송사 관리
        <b-button
          v-if="!isShowAdd"
          size="sm"
          class="float-right"
          variant="primary"
          @click="isShowAdd = true"
          :disabled="isLoading"
        >
          + 추가
        </b-button>
      </template>
      <b-table-simple ref="table" responsive bordered>
        <b-thead head-variant="light">
          <b-tr>
            <b-th>순서</b-th>
            <b-th>배송사</b-th>
            <!-- <b-th>삭제</b-th> -->
          </b-tr>
        </b-thead>
        <b-tbody>
          <b-tr v-for="item in deliveryProviders" :key="item.id">
            <b-td>
              {{ item.value }}
            </b-td>
            <b-td>
              {{ item.text }}
            </b-td>
            <!-- <b-td>
              <b-button-group>
                <b-button
                  v-if="item.id == 0"
                  size="md"
                  variant="success"
                  @click="createItem(item)"
                >
                  생성
                </b-button>
                <b-button
                  size="md"
                  variant="danger"
                  @click="deleteItem(item)"
                >
                  삭제
                </b-button>
              </b-button-group>
            </b-td> -->
          </b-tr>
        </b-tbody>
        <!-- <b-tfoot>
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
        </b-tfoot> -->
      </b-table-simple>
    </b-card>
  </b-container>
</template>

<script>
export default {
  data() {
    return {
      isShowAdd: false,
      isLoading: false,
      provider: null,
      //deliveryProviders: JSON.parse(JSON.stringify(this.$store.getters.deliveryProviders)),
    };
  },
  computed: {
    isEnableAddItem() {
      return true;
      //return this.shopTypes.filter(row => row.id === 0).length < 1
    },
    deliveryProviders() {
      return this.$store.getters.deliveryProviders;
    },
  },
  methods: {
    async addProvider() {
      if (!this.provider) {
        this.$notify({
          group: "top-center",
          type: "error",
          title: "배송사를 입력해주세요",
        });
        return;
      }
      this.isLoading = true;
      try {
        await this.$store.dispatch("post", {
          api: "config/deliveryProviders",
          payload: {
            provider: this.provider,
          },
        });
        this.$store.dispatch("deliveryProviders");
        this.provider = null;
        this.isShowAdd = false;

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
    addItem() {
      const deliveryProvider = {
        text: "",
        value: null,
      };
      this.deliveryProviders = [...this.deliveryProviders, deliveryProvider];
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