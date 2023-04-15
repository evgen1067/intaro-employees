import { defineAsyncComponent } from 'vue';

export const defaultConfig = {
  responsive: true,
  scales: {
    y: {
      ticks: {
        display: true,
        font: {
          fontSize: 14,
          family: 'Montserrat Alternates',
        },
      },
    },
    x: {
      ticks: {
        display: true,
        font: {
          fontSize: 14,
          family: 'Montserrat Alternates',
        },
      },
    },
  },
  plugins: {
    legend: {
      display: true,
      position: 'bottom',
      labels: {
        font: {
          family: 'Montserrat Alternates',
          size: 14,
        },
      },
    },
    tooltip: {
      titleFont: {
        size: 14,
        family: 'Montserrat Alternates',
      },
      bodyFont: {
        size: 14,
        family: 'Montserrat Alternates',
      },
      boxPadding: 4,
    },
  },
  datasets: {
    line: {
      pointRadius: 10,
    },
    bubble: {
      borderColor: 'transparent',
    },
    bar: {
      borderColor: 'transparent',
    },
    pie: {
      plugins: {
        legend: {
          display: true,
        },
      },
    },
  },
  maintainAspectRatio: false,
  animation: true,
};

export const chartTypesMap = {
  line: defineAsyncComponent(() => import('./chart-types/LineChart.vue')),
  pie: defineAsyncComponent(() => import('./chart-types/PieChart.vue')),
  bar: defineAsyncComponent(() => import('./chart-types/BarChart.vue')),
  'horizontal-bar': defineAsyncComponent(() => import('./chart-types/HorizontalBarChart.vue')),
  'stacked-bar': defineAsyncComponent(() => import('./chart-types/StackedBarChart.vue')),
  'multi-pie': defineAsyncComponent(() => import('./chart-types/MultiSeriesPie.vue')),
};
