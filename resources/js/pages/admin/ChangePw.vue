<template>
  <b-container>
    <b-row class="justify-content-center">
      <b-col md="8">
        <b-card>
          <template v-slot:header>
            비밀번호 변경        
          </template>
          <b-form @submit.prevent="changePwAction">
            <!-- <b-form-group
              label="현재비밀번호"
              description="6자리이상 입력해주세요"
              :state="$v.form.password.$dirty ? !$v.form.password.$error : null"
            >
              <b-form-input
                v-model="form.password"
                placeholder="현재 비밀번호"
                :state="$v.form.password.$dirty ? !$v.form.password.$error : null"
              />
            </b-form-group> -->
            <b-form-group
              label="변경할 비밀번호"
              description="6자리이상 입력해주세요"
            >
              <b-form-input
                v-model="form.new_password"
                placeholder="변경할 비밀번호"
                :state="$v.form.new_password.$dirty ? !$v.form.new_password.$error : null"
              />
            </b-form-group>
            <div class="float-right">
              <b-button
                type="submit"
                variant="primary"
                :disabled="isLoading"
              >
                변경
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
  mixins: [validationMixin],
  data(){
    return {
      isLoading: false,
      form: {
        password: null,
        new_password: null
      }
    }
  },
  validations: {
    form: {
      new_password: {
        required,
        minLength: minLength(6)
      },
      // password: {
      //   required,
      //   minLength: minLength(6)
      // }
    }
  },
  methods: {
    async changePwAction(){
      this.$v.form.$touch()
      if (this.$v.form.$anyError) {
        this.$notify({
          group: "top-center",
          type: "error",
          title: "필수값을 입력해주세요."
        })
        return
      }

      this.isLoading = true
      try {
        await this.$store.dispatch("post", {
          api: "auth/changePw",
          payload: this.form
        })
        this.$notify({
          group: "top-center",
          type: "success",
          title: "비밀번호가 변경되었습니다. 다시로그인해주세요"
        })
        this.form.new_password = null

        this.$store.dispatch("logout")
        this.$router.replace({name: "AdminLogin"})
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