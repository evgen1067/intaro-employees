<template>
  <div class="h-100">
    <va-sidebar :minimized="minimized" :width="width" gradient>
      <template v-for="(item, key) in routes" :key="key">
        <va-sidebar-item :active="isRouteActive(item.name)" :active-color="colors.primary" @click="toRoute(item.name)">
          <va-sidebar-item-content>
            <va-icon :name="item.meta.icon" />
            <va-sidebar-item-title>
              {{ item.title }}
            </va-sidebar-item-title>
          </va-sidebar-item-content>
        </va-sidebar-item>
      </template>
    </va-sidebar>
  </div>
</template>

<script>
import { appRoutes} from "../../helpers/constants";
import { VaIcon, VaSidebar, VaSidebarItem, VaSidebarItemContent, VaSidebarItemTitle } from 'vuestic-ui';
export default {
  name: 'AppSidebar',
  data: () => ({
    colors: {
      primary: '#702cf9',
      secondary: '#894eff',
    },
  }),
  props: {
    minimized: {
      type: Boolean,
      required: true,
    },
    width: {
      type: String,
      required: false,
      default: '350px',
    },
  },
  components: { VaIcon, VaSidebarItemContent, VaSidebarItemTitle, VaSidebarItem, VaSidebar },
  methods: {
    toRoute(routeName) {
      this.$router.push({
        name: routeName,
      });
    },
    isRouteActive(route) {
      return route === this.$route.name;
    },
  },
  computed: {
    routes() {
      return appRoutes;
    },
  },
};
</script>

<style lang="scss" scoped>
@import 'AppSidebar';
</style>
