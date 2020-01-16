<template>
  <b-container>
    <b-row
      align-v="center"
      class="pt-5"
    >
      <b-col align-self="center">
        <b-card
          title="관리자 로그인"
        >
          <b-form @submit.prevent="loginAction">
            <b-form-group>
              <b-form-input
                placeholder="아이디"
                v-model="$v.form.email.$model"
                :state="$v.form.email.$dirty ? !$v.form.email.$error : null"
              />
              <b-form-invalid-feedback>
                항목을 채워주세요.
              </b-form-invalid-feedback>
            </b-form-group>
            <b-form-group>
              <b-form-input
                type="password"
                placeholder="비밀번호"
                v-model="$v.form.password.$model"
                :state="$v.form.password.$dirty ? !$v.form.password.$error : null"
              />
              <b-form-invalid-feedback>
                항목을 채워주세요.(최소6글자이상)
              </b-form-invalid-feedback>
            </b-form-group>
            <div class="text-right">
              <b-button
                type="submit"
                variant="primary"
              >
                로그인
              </b-button>
            </div>
          </b-form>
        </b-card>
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import { validationMixin } from "vuelidate"
import { required, minLength } from "vuelidate/lib/validators"
export default {
  data(){
    return {
      form: {
        email: null,
        password: null
      },
      isLoading: false,
    }
  },
  validations: {
    form: {
      email: {
        required
      },
      password: {
        required,
        minLength: minLength(6)
      }
    }
  },
  methods: {
    async loginAction(){
      this.$v.form.$touch()
      if (this.$v.form.$anyError) {
        return
      }

      this.isLoading = true

      try {
        const payload = this.form
        let { success, error } = await this.$store.dispatch("login", payload)
        if (success) {
          this.$router.push({name: "adminHome"})
        } else {
          this.$notify({
            type: "error",
            group: "top-center",
            title: error.message
          })
        }
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