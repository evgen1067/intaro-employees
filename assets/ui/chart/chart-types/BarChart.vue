<template>
  <Bar :chart-options="{ ...chartOptions }" :chart-data="chartData" />
</template>

<script>
import { Bar } from 'vue-chartjs';

export default {
  name: 'BarChart',
  components: {
    Bar,
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
