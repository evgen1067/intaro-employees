<template>
  <main-index />
</template>

<script>
import MainIndex from './pages/index/MainIndex.vue';

export default {
  name: 'App',
  components: { MainIndex },
};
</script>

<style lang="scss">
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Montserrat Alternates', sans-serif;
}
</style>
