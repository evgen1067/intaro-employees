<template>
  <div class="app-layout">
    <div class="app-layout__content">
      <div class="app-layout__page">
        <div class="layout fluid">
          <router-view />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ErrorLayout',
};
</script>

<style lang="scss" scoped>
@import '../index';
</style>
