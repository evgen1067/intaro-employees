<template>
  <template v-if="!loading">
    <va-form ref="authForm" @validation="validation = $event" @submit.prevent="handleSubmit" class="d-flex flex-column">
      <va-input v-model="email" class="mb-3" type="email" label="Email" :rules="emailRule" />

      <va-input v-model="password" class="mb-3" type="password" label="Пароль" :rules="passwordRule" />

      <div class="d-flex justify-center mt-3">
        <va-button class="my-0" @click="handleSubmit">Войти</va-button>
      </div>
    </va-form>
  </template>
  <load-spinner v-else />
</template>

<script>
import { LoadSpinner } from '../../../ui';
import { userSymbol } from '../../../store';
import { appRoutes } from '../../../helpers/constants';
import { VaButton, VaForm, VaInput } from 'vuestic-ui';
export default {
  name: 'LoginPage',
  components: { VaButton, VaInput, VaForm, LoadSpinner },
  inject: {
    userState: {
      from: userSymbol,
    },
  },
  mounted() {
    if (this.user) this.$router.push({ name: appRoutes.employee.name });
    this.$refs.authForm.focus();
  },
  data: () => ({
    email: '',
    password: '',
    emailRule: [value => value.trim().length > 0 || 'Email не может быть пустым'],
    passwordRule: [value => value.trim().length > 0 || 'Пароль не может быть пустым'],
    validation: null,
    loading: false,
  }),
  computed: {
    user() {
      return this.userState.state.user;
    },
  },
  methods: {
    toast(message, color) {
      this.$vaToast.init({
        message: message,
        color: color,
        position: 'bottom-right',
      });
    },
    async handleSubmit() {
      this.validation = this.$refs.authForm.validate();
      if (this.validation) {
        this.loading = true;
        let data = {
          username: this.email,
          password: this.password,
        };
        const result = await this.userState.loginUser(data);
        if (!result?.token) {
          this.loading = false;
          if (result.code === 401) {
            this.toast('Проверьте правильность введенных данных', 'danger');
          } else {
            this.toast(result.message, 'danger');
          }
        } else {
          this.$router.push({ name: appRoutes.employee.name });
        }
      }
    },
  },
};
</script>

<style scoped></style>
