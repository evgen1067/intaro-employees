<template>
  <template v-if="!loading">
    <error-layout v-if="[notFoundRouteName, errorRouteName].includes(routeName)" />
    <login-layout v-else-if="[registerRouteName, loginRouteName].includes(routeName)" />
    <standard-layout v-else />
  </template>
  <load-spinner v-else />
</template>

<script>
import { ErrorLayout, StandardLayout, LoginLayout } from './layouts';
import { errorRoute, loginRoute, notFoundRoute, registerRoute } from '../../helpers/constants';
import { LoadSpinner } from '../../ui';
import { userSymbol } from '../../store';
export default {
  name: 'MainIndex',
  components: { LoadSpinner, LoginLayout, StandardLayout, ErrorLayout },
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
    errorRouteName() {
      return errorRoute.name;
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

<style lang="scss" scoped></style>
