<template>
  <div class="app-layout">
    <app-header :is-sidebar-minimized="isSidebarMinimized" @toggle-sidebar="isSidebarMinimized = !isSidebarMinimized" />
    <div class="app-layout__content">
      <div class="app-layout__sidebar-wrapper" :class="{ minimized: isSidebarMinimized }">
        <app-sidebar width="320px" :minimized="isSidebarMinimized"></app-sidebar>
      </div>
      <div class="app-layout__page">
        <div class="layout fluid va-gutter-5">
          <router-view />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import AppHeader from '../../../ui/header/AppHeader.vue';
import AppSidebar from '../../../ui/sidebar/AppSidebar.vue';
export default {
  name: 'StandardLayout',
  components: { AppHeader, AppSidebar },
  data: () => ({
    isSidebarMinimized: true,
  }),
};
</script>

<style lang="scss" scoped>
@import '../MainIndex';
</style>
