<template>
  <b-container>
    <b-card>
      <template v-slot:header>
        거래처 생성/수정 및 거래처별 주문내역 관리
        <b-button
          variant="primary"
          size="sm"
          class="float-right"
          @click="$bvModal.show('modal-user-register')"
        >
          거래처생성
        </b-button>
      </template>
      <b-table striped hover :fields="fields" :items="users" :busy="isLoading">
        <template v-slot:cell(index)="data">
          {{ data.index + 1 }}
        </template>
        <template v-slot:cell(user)="data">
          <router-link
            :to="{ name: 'AdminUser', params: { id: data.value.id } }"
          >
            {{ data.value.email }}
          </router-link>
        </template>
        <template v-slot:cell(shop_type_id)="data">
          {{ getShopType(data.value) }}
        </template>
        <template v-slot:cell(trade_link_id)="data">
          <router-link
            :to="{ name: 'AdminUserTrades', params: { id: data.value } }"
          >
            거래내역확인
          </router-link>
        </template>

        <template v-slot:table-busy>
          <div class="text-center text-danger my-2">
            <b-spinner class="align-middle" />
            <strong>Loading...</strong>
          </div>
        </template>
        <template v-slot:table-caption>
          거래처 생성/수정 및 거래처별 주문내역 관리
        </template>
      </b-table>
    </b-card>

    <b-modal
      id="modal-user-register"
      title="거래처 생성"
      content-class="shadow"
      centered
      hide-footer
    >
      <b-form @submit.prevent="addUserAction">
        <b-form-group label="거래처이름">
          <b-form-input
            type="text"
            placeholder="거래처이름"
            v-model="$v.form.name.$model"
            :state="$v.form.name.$dirty ? !$v.form.name.$error : null"
          />
        </b-form-group>
        <b-form-group label="거래처아이디">
          <b-form-input
            type="text"
            placeholder="거래처아이디"
            v-model="$v.form.email.$model"
            :state="$v.form.email.$dirty ? !$v.form.email.$error : null"
          />
        </b-form-group>
        <b-form-group label="비밀번호">
          <b-form-input
            type="text"
            placeholder="비밀번호"
            v-model="$v.form.password.$model"
            :state="$v.form.password.$dirty ? !$v.form.password.$error : null"
          />
        </b-form-group>
        <b-form-group label="거래처타입">
          <b-form-select
            :options="shopTypes"
            v-model="$v.form.shop_type_id.$model"
            :state="
              $v.form.shop_type_id.$dirty ? !$v.form.shop_type_id.$error : null
            "
          />
        </b-form-group>
        <div class="float-right">
          <b-button
            variant="primary"
            type="submit"
            size="sm"
            :disabled="isLoading"
          >
            거래처생성
          </b-button>
        </div>
      </b-form>
    </b-modal>
  </b-container>
</template>

<script>
import { validationMixin } from "vuelidate";
import { required } from "vuelidate/lib/validators";
export default {
  mixins: [validationMixin],
  data() {
    return {
      isLoading: false,
      fields: [
        {
          key: "index",
          label: "순서",
        },
        {
          key: "user",
          label: "아이디",
        },
        {
          key: "name",
          label: "거래처이름",
        },
        {
          key: "shop_type_id",
          label: "거래처타입",
        },
        {
          key: "created_at",
          label: "생성일",
        },
        {
          key: "trade_link_id",
          label: "거래내역관리",
        },
      ],
      users: [],
      form: {
        name: null,
        email: null,
        password: null,
        shop_type_id: null,
      },
      show: true,
    };
  },
  validations: {
    form: {
      name: {
        required,
      },
      email: {
        required,
      },
      password: {
        required,
      },
      shop_type_id: {
        required,
      },
    },
  },
  computed: {
    shopTypes() {
      let shopTypes = this.$store.getters.shopTypes;
      return [
        { value: null, text: "거래처타입" },
        ...shopTypes.map((row) => {
          return {
            value: row.id,
            text: row.type,
          };
        }),
      ];
    },
  },
  mounted() {
    this.getUsers();
  },
  methods: {
    getShopType(value) {
      let shop = this.shopTypes.find((row) => row.value == value);
      return shop ? shop.text : null;
    },
    async getUsers() {
      this.isLoading = true;
      try {
        let { users } = await this.$store.dispatch("get", {
          api: "users",
          payload: {},
        });
        this.users = users.data.map((item, idx) => {
          item.trade_link_id = item.id;
          item.user = {
            id: item.id,
            email: item.email,
          };
          return item;
        });
      } catch (e) {
        console.log(e);
      } finally {
        this.isLoading = false;
      }
    },
    async addUserAction() {
      this.$v.form.$touch();
      if (this.$v.form.$anyError) {
        this.$notify({
          group: "top-center",
          type: "error",
          title: "필수값을 입력해주세요.",
        });
        return;
      }

      this.isLoading = true;
      try {
        let { user } = await this.$store.dispatch("post", {
          api: "user",
          payload: this.form,
        });
        user.user = {
          id: user.id,
          email: user.email,
        };

        this.users.push(user);
        this.$bvModal.hide("modal-user-register");
      } catch (e) {
        let { name, email, password } = e.response.data.data;

        this.$notify({
          group: "top-center",
          type: "error",
          title: name || email || password,
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