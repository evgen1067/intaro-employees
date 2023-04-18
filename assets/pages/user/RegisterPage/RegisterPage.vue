<template>
  <template v-if="!loading">
    <va-form ref="registerForm" @submit.prevent="handleSubmit" class="d-flex flex-column">
      <va-select
        v-model="request.roles"
        :options="roles"
        :rules="roleRule"
        class="w-100 mb-3"
        clearable
        color="#4056A1"
        label="Роль"
        max-height="150px"
        search-placeholder-text="Поиск"
        searchable
        selected-top-shown
        text-by="label"
        value-by="listValueId"
      />

      <va-select
        v-if="request.roles === 'ROLE_DEPARTMENT_MANAGER'"
        v-model="request.departments"
        :max-visible-options="1"
        :options="departments"
        :rules="departmentsRule"
        class="w-100 mb-3"
        clearable
        color="#4056A1"
        label="Отделы"
        max-height="150px"
        multiple
        search-placeholder-text="Поиск"
        searchable
        selected-top-shown
        text-by="label"
        value-by="listValueId"
      />

      <va-select
        v-if="request.roles === 'ROLE_HR_MANAGER'"
        v-model="request.companies"
        :max-visible-options="1"
        :options="companies"
        :rules="companiesRule"
        class="w-100 mb-3"
        clearable
        color="#4056A1"
        label="Компании"
        max-height="150px"
        multiple
        search-placeholder-text="Поиск"
        searchable
        selected-top-shown
        text-by="label"
        value-by="listValueId"
      />

      <va-input v-model="request.name" :rules="nameRule" class="mb-3" label="Имя" type="text" />

      <va-input v-model="request.email" :rules="emailRule" class="mb-3" label="Email" type="email" />

      <va-input v-model="request.password" :rules="passwordRule" class="mb-3" label="Пароль" type="password" />

      <div class="d-flex justify-center mt-3">
        <va-button class="my-0" @click="handleSubmit">Зарегистрировать</va-button>
      </div>
    </va-form>
  </template>
  <load-spinner v-else />
</template>

<script>
import { LoadSpinner } from '../../../ui';
import { VaButton, VaForm, VaInput, VaSelect } from 'vuestic-ui';
import { userSymbol } from '../../../store';
import { EmployeeApi, UserApi } from '../../../api';
import { appRoutes } from '../../../helpers/constants';

export default {
  name: 'RegisterPage',
  components: { VaSelect, VaButton, VaInput, VaForm, LoadSpinner },
  inject: {
    userState: {
      from: userSymbol,
    },
  },
  async created() {
    const result = await Promise.all([
      EmployeeApi.getCompanies(this.user.token),
      EmployeeApi.getDepartments(this.user.token),
    ]);
    this.companies = result[0].data;
    this.departments = result[1].data;
    this.loading = false;
  },
  data: () => ({
    loading: true,
    validation: null,
    request: {
      name: '',
      email: '',
      password: '',
      companies: [],
      departments: [],
      roles: '',
    },
    roles: [
      {
        label: 'Топ менеджер',
        listValueId: 'ROLE_TOP_MANAGER',
      },
      {
        label: 'HR менеджер',
        listValueId: 'ROLE_HR_MANAGER',
      },
      {
        label: 'Руководитель отдела',
        listValueId: 'ROLE_DEPARTMENT_MANAGER',
      },
    ],
    departments: [],
    companies: [],
    roleRule: [value => value.toString().trim().length > 0 || 'Необходимо выбрать роль создаваемого пользователя'],
    nameRule: [value => value.trim().length > 0 || 'Имя не может быть пустым'],
    emailRule: [value => value.trim().length > 0 || 'Email не может быть пустым'],
    passwordRule: [value => value.trim().length > 0 || 'Пароль не может быть пустым'],
    companiesRule: [
      value =>
        (Array.isArray(value) && value.length > 0) || 'Необходимо выбрать компании, к которым менеджер имеет доступ',
    ],
    departmentsRule: [
      value =>
        (Array.isArray(value) && value.length > 0) || 'Необходимо выбрать отделы, к которым менеджер имеет доступ',
    ],
  }),
  methods: {
    toast(message, color) {
      this.$vaToast.init({
        message: message,
        color: color,
        position: 'bottom-right',
      });
    },
    validate() {
      if (this.request.roles === 'ROLE_SUPER_ADMIN') {
        return (
          this.request.name.trim().length > 0 &&
          this.request.email.trim().length > 0 &&
          this.request.password.trim().length > 0
        );
      } else if (this.request.roles === 'ROLE_HR_MANAGER') {
        return (
          this.request.name.trim().length > 0 &&
          this.request.email.trim().length > 0 &&
          this.request.password.trim().length > 0 &&
          this.request.companies.length > 0
        );
      } else if (this.request.roles === 'ROLE_DEPARTMENT_MANAGER') {
        return (
          this.request.name.trim().length > 0 &&
          this.request.email.trim().length > 0 &&
          this.request.password.trim().length > 0 &&
          this.request.departments.length > 0
        );
      } else {
        return (
          this.request.name.trim().length > 0 &&
          this.request.email.trim().length > 0 &&
          this.request.password.trim().length > 0 &&
          this.request.roles.length > 0
        );
      }
    },
    async handleSubmit() {
      this.validation = this.$refs.registerForm.validate();
      if (this.validation && this.validate()) {
        const result = (await UserApi.register(this.user.token, this.request)).data;
        if (result.code !== 201) {
          this.toast(result.message, 'danger');
        } else {
          this.toast(result.message, 'success');
          this.$router.push({ name: appRoutes.employee.name });
        }
      }
    },
  },
  computed: {
    user() {
      return this.userState.state.user;
    },
  },
};
</script>

<style scoped></style>
