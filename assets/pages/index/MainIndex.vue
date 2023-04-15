<template>
  <template v-if="!loading">
    <not-found-layout v-if="routeName === notFoundRouteName" />
    <login-layout v-else-if="routeName === loginRouteName || routeName === registerRouteName" />
    <standard-layout v-else />
  </template>
  <load-spinner v-else />
</template>

<script>
import NotFoundLayout from './Layouts/NotFoundLayout.vue';
import { loginRoute, notFoundRoute, registerRoute } from '../../helpers/constants';
import StandardLayout from './Layouts/StandardLayout.vue';
import LoginLayout from './Layouts/LoginLayout.vue';
import LoadSpinner from '../../ui/spinner/LoadSpinner.vue';
import { userSymbol } from '../../store';
export default {
  name: 'MainIndex',
  components: { LoadSpinner, LoginLayout, StandardLayout, NotFoundLayout },
  data: () => ({
    loading: true,
  }),
  inject: {
    userState: {
      from: userSymbol,
    },
  },
  async mounted() {
    if (!this.user) {
      const tokenFromStorage = localStorage?.token;
      if (tokenFromStorage && tokenFromStorage !== 'null') {
        const result = await this.userState.fetchUser(localStorage.token);
        if (result?.data?.code === 401) {
          await this.userState.logoutUser();
          this.$router.push({ name: loginRoute.name });
        }
      } else {
        this.$router.push({ name: loginRoute.name });
      }
    }
    this.loading = false;
  },
  computed: {
    registerRouteName() {
      return registerRoute.name;
    },
    loginRouteName() {
      return loginRoute.name;
    },
    notFoundRouteName() {
      return notFoundRoute.name;
    },
    routeName() {
      return this.$route.name;
    },
    user() {
      return this.userState.state.user;
    },
  },
};
</script>

<style lang="scss">
@import './MainIndex';
</style>
