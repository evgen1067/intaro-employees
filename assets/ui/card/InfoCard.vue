<template>
  <va-card :color="color" gradient>
    <va-card-content>
      <h3 class="va-h3" style="color: white">{{ value }}</h3>
      <p style="color: white">{{ description }}</p>
    </va-card-content>
  </va-card>
</template>

<script>
import { VaCard, VaCardContent } from 'vuestic-ui';
export default {
  name: 'InfoCard',
  components: { VaCardContent, VaCard },
  props: {
    value: {
      required: true,
      type: Number,
    },
    description: {
      required: true,
      type: String,
    },
    color: {
      type: String,
      required: false,
      default: '#4056A1',
    },
  },
};
</script>

<style scoped>
p {
    margin-bottom: 0;
}
.va-card__content {
    padding: 0.8rem;
}
</style>