<template>
  <component :is="chartComponent" :chart-data="data" :chart-options="chartOptions" class="va-chart" />
</template>

<script>
import { defaultConfig, chartTypesMap } from './ChartConfig';
export default {
  name: 'AppChart',
  props: {
    type: {
      type: String,
      required: true,
    },
    data: {
      type: Object,
      required: true,
    },
  },
  computed: {
    chartComponent() {
      return chartTypesMap[this.type];
    },
    chartOptions() {
      return { ...defaultConfig };
    },
  },
};
</script>

<style lang="scss" scoped>
@import 'Chart';
</style>
