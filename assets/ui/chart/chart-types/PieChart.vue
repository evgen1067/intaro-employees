<template>
  <Pie :chart-options="{ ...chartOptions }" :chart-data="chartData" />
</template>

<script>
import { Pie } from 'vue-chartjs';
export default {
  name: 'PieChart',
  components: { Pie },
  props: {
    chartData: {
      type: Object,
      required: true,
    },
    chartOptions: {
      type: Object,
      required: true,
    },
  },
};
</script>

<style scoped></style>
