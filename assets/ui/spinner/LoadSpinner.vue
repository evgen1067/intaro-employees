<template>
  <div class="d-flex align-items-center align-content-center justify-content-center p-5">
    <breeding-rhombus-spinner :animation-duration="2000" :size="200" color="#702cf9" />
  </div>
</template>

<script>
import { BreedingRhombusSpinner } from 'epic-spinners';
export default {
  name: 'LoadSpinner',
  components: {
    BreedingRhombusSpinner,
  },
};
</script>

<style scoped></style>
