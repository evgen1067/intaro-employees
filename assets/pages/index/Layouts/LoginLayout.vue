<template>
  <div class="auth-layout row align-content-center">
    <div class="flex xs12 pa-3 justify-center">
      <router-link class="my-5 justify-center d-flex" :to="{ name: loginRouteName }">
        <intaro-icon class="icon__intaro" />
      </router-link>
    </div>

    <div class="flex pa-3">
      <div class="d-flex justify-center">
        <va-card class="auth-layout__card">
          <va-card-content>
            <va-separator />

            <div class="pa-3">
              <router-view />
            </div>
          </va-card-content>
        </va-card>
      </div>
    </div>
  </div>
</template>

<script>
import { VaCard, VaCardContent, VaSeparator } from 'vuestic-ui';
import { loginRoute } from '../../../helpers/constants';
import IntaroIcon from '../../../ui/icons/intaro/IntaroIcon.vue';
export default {
  name: 'LoginLayout',
  components: { VaSeparator, VaCardContent, VaCard, IntaroIcon },
  computed: {
    loginRouteName() {
      return loginRoute.name;
    },
  },
};
</script>

<style lang="scss" scoped>
@import '../MainIndex';
</style>
