<template>
  <Bar :chart-options="{ ...chartOptions, ...stackedBarOptions }" :chart-data="chartData" />
</template>

<script>
import { Bar } from 'vue-chartjs';

export default {
  name: 'StackedBarChart',
  components: {
    Bar,
  },
  data: () => ({
    stackedBarOptions: {
      indexAxis: 'y',
      elements: {
        bar: {
          borderWidth: 1,
        },
      },
      scales: {
        x: {
          stacked: true,
          ticks: {
            display: true,
            font: {
              fontSize: 14,
              family: 'Montserrat Alternates',
            },
          },
        },
        y: {
          stacked: true,
          ticks: {
            display: true,
            font: {
              fontSize: 14,
              family: 'Montserrat Alternates',
            },
          },
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
