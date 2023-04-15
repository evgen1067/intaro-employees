<template>
  <Line :chart-options="{ ...chartOptions }" :chart-data="chartData" />
</template>

<script>
import { Line } from 'vue-chartjs';
export default {
  name: 'LineChart',
  components: {
    Line,
  },
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
