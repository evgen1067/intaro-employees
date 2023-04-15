<template>
  <div class="notfound-wrapper">
    <div class="notfound-bg"></div>
    <div class="notfound">
      <div class="notfound-404">
        <h1>404</h1>
      </div>
      <h2>Мы сожалеем, но запрошенная вами страница не была найдена.</h2>
      <router-link :to="{ name: 'employee' }" class="home-btn">На главную</router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotFoundPage',
};
</script>

<style lang="scss" scoped>
@import 'NotFoundPage.scss';
</style>
