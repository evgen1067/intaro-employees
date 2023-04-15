<template>
  <Bar :chart-options="{ ...chartOptions, ...horizontalBarOptions }" :chart-data="chartData" />
</template>

<script>
import { Bar } from 'vue-chartjs';

export default {
  name: 'HorizontalBarChart',
  components: {
    Bar,
  },
  data: () => ({
    horizontalBarOptions: {
      indexAxis: 'y',
      elements: {
        bar: {
          borderWidth: 1,
        },
      },
    },
  }),
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
