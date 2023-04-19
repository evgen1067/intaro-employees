<template>
  <div class="notfound-wrapper">
    <div class="notfound-bg"></div>
    <div class="notfound">
      <div class="notfound-404">
        <h1>5xx</h1>
      </div>
      <h2>Ой, что-то случилось. Всегда есть время для перерыва на кофе. Мы должны вернуться к тому времени, как ты допьешь свой кофе.</h2>
      <router-link :to="{ name: 'employee' }" class="home-btn">На главную</router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ErrorPage',
};
</script>

<style lang="scss" scoped>
@import '../Error';
</style>
