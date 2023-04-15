<template>
  <va-navbar class="app-layout-navbar" gradient color="#702cf9" shape>
    <template #left>
      <div class="left">
        <menu-icon
          class="va-navbar__item cursor-pointer ms-1"
          :class="{ 'x-flip': !isSidebarMinimized }"
          @click="toggleSidebar"
        />
        <logo-icon class="ms-2" />
      </div>
    </template>
    <template #right>
      <router-link v-if="isGrantedAdmin" :to="{ name: registerRoute.name }" class="register-link me-3"
        >Регистрация</router-link
      >
      <div class="profile-dropdown-wrapper">
        <va-dropdown v-model="isShown" class="profile-dropdown" stick-to-edges placement="bottom" :offset="[13, 0]">
          <template #anchor>
            <span class="profile-dropdown__anchor">
              {{ user?.name }}
              <font-awesome-icon :icon="isShown ? 'angle-up' : 'angle-down'" />
            </span>
          </template>
          <va-dropdown-content class="profile-dropdown__content">
            <va-list-item class="pa-2">
              <div class="profile-dropdown__item" @click="logout">Выход</div>
            </va-list-item>
          </va-dropdown-content>
        </va-dropdown>
      </div>
    </template>
  </va-navbar>
</template>

<script>
import { VaNavbar } from 'vuestic-ui';
import { appRoutes, loginRoute, registerRoute } from '../../helpers/constants';
import LogoIcon from '../icons/logo/LogoIcon.vue';
import MenuIcon from '../icons/menu/MenuIcon.vue';
import { userSymbol } from '../../store';
export default {
  name: 'AppHeader',
  props: {
    isSidebarMinimized: {
      required: true,
      type: Boolean,
    },
  },
  inject: {
    userState: {
      from: userSymbol,
    },
  },
  data: () => ({
    isShown: false,
  }),
  components: { LogoIcon, MenuIcon, VaNavbar },
  emits: ['toggleSidebar'],
  methods: {
    toggleSidebar() {
      this.$emit('toggleSidebar');
    },
    toRoute(routeName) {
      this.$router.push({
        name: routeName,
      });
    },
    async logout() {
      await this.userState.logoutUser();
      this.$router.push({ name: loginRoute.name });
    },
    isRouteActive(route) {
      return route === this.$route.name;
    },
  },
  computed: {
    registerRoute() {
      return registerRoute;
    },
    routes() {
      return appRoutes;
    },
    user() {
      return this.userState.state.user;
    },
    isGrantedAdmin() {
      return (
        this.user &&
        this.user.roles.length > 0 &&
        (this.user.roles.includes('ROLE_SUPER_ADMIN') || this.user.roles.includes('ROLE_TOP_MANAGER'))
      );
    },
  },
};
</script>

<style lang="scss" scoped>
@import 'AppHeader';
</style>
