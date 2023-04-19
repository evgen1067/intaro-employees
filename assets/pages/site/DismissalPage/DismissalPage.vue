<template>
  <template v-if="!loading">
    <div class="d-flex flex-column">
      <div class="row mb-3 col-12">
        <div class="col-2">
          <va-date-input
            v-model="filter.from"
            label="От"
            manual-input
            clearable
            :reset-on-close="false"
            :format="formatFn"
            class="w-100"
          />
        </div>
        <div class="col-2">
          <va-date-input
            v-model="filter.to"
            label="До"
            manual-input
            clearable
            :reset-on-close="false"
            :format="formatFn"
            class="w-100"
          />
        </div>
      </div>
      <div class="row mb-3 col-12">
        <div class="col-2">
          <info-card :value="dismissals.totalCount" description="число сотрудников" color="#4056A1" class="h-100" />
        </div>
        <div class="col-2">
          <info-card :value="dismissals.totalAccepted" description="принято" color="success" class="h-100" />
        </div>
        <div class="col-2">
          <info-card description="Уволено" :value="dismissals.totalDismissed" color="#F13C20" class="h-100" />
        </div>
        <div class="col-2">
          <info-card :value="dismissals.avgWorkExp" description="стаж работы" color="#4056A1" class="h-100" />
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-6">
          <va-card>
            <va-card-content>
              <app-chart :data="workExpChart" type="bar" />
            </va-card-content>
          </va-card>
        </div>
        <div class="col-6">
          <va-card>
            <va-card-content>
              <app-chart :data="genderChart" type="pie" />
            </va-card-content>
          </va-card>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-12">
          <va-card>
            <va-card-content>
              <app-chart :data="predictionChart" type="line" />
            </va-card-content>
          </va-card>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-12 col-xxl-5">
          <va-card>
            <va-card-content>
              <app-chart :data="gradeChart" type="bar" />
            </va-card-content>
          </va-card>
        </div>
        <div class="col-12 col-xxl-7">
          <va-card>
            <va-card-content>
              <app-chart :data="competenceChart" type="bar" />
            </va-card-content>
          </va-card>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-12 col-xxl-5">
          <va-card>
            <va-card-content>
              <app-chart style="height: 850px" :data="departmentChart" type="horizontal-bar" />
            </va-card-content>
          </va-card>
        </div>
        <div class="col-12 col-xxl-7">
          <va-card>
            <va-card-content>
              <app-chart style="height: 850px" :data="positionChart" type="horizontal-bar" />
            </va-card-content>
          </va-card>
        </div>
      </div>
    </div>
  </template>
  <load-spinner style="min-height: 75vh" v-else />
</template>

<script>
import { LoadSpinner, InfoCard, AppChart } from '../../../ui';
import { VaCard, VaCardContent, VaDateInput } from 'vuestic-ui';
import { AnalyticsApi } from '../../../api';
import randomColor from 'randomcolor';
import { userSymbol } from '../../../store';
import { errorRoute } from '../../../helpers/constants';
export default {
  name: 'DismissalPage',
  components: { VaCard, VaCardContent, AppChart, InfoCard, VaDateInput, LoadSpinner },
  inject: {
    userState: {
      from: userSymbol,
    },
  },
  data: () => ({
    loading: false,
    filter: {
      from: '',
      to: '',
    },
    dismissals: null,
  }),
  watch: {
    filter: {
      immediate: true,
      deep: true,
      async handler() {
        if (!this.loading) {
          await this.fetchDismissalData();
        }
      },
    },
  },
  created() {
    this.defaultFilter();
  },
  methods: {
    async defaultFilter() {
      this.filter = {
        from: (new Date()).setMonth(this.filter.from.getMonth() - 12),
        to: new Date(),
      };
    },
    async fetchDismissalData() {
      this.loading = true;
      const result = await AnalyticsApi.getDismissal(this.user.token, this.clearFilter());
      if (result.status) {
        this.dismissals = result.data;
        this.toast('Данные загружены', 'success');
      } else {
        this.toast(result.data, 'danger');
        this.$router.push({ name: errorRoute.name });
      }
      this.loading = false;
    },
    formatFn(date) {
      return date.toLocaleDateString('ru-RU');
    },
    clearFilter() {
      let valueTo = this.filter.to,
        valueFrom = this.filter.from,
        flag = false;
      if (valueTo < valueFrom) {
        valueFrom = new Date();
        valueFrom.setMonth(valueFrom.getMonth() - 36);
        valueTo = new Date();
        flag = true;
      }
      if (!valueFrom) {
        valueFrom = new Date();
        valueFrom.setMonth(valueFrom.getMonth() - 36);
        flag = true;
      }
      if (!valueTo) {
        valueTo = new Date();
        flag = true;
      }
      if (flag) {
        this.filter.to = valueTo;
        this.filter.from = valueFrom;
      }
      return {
        valueFrom: this.filter.from.toLocaleDateString('ru-RU'),
        valueTo: this.filter.to.toLocaleDateString('ru-RU'),
      };
    },
    getColors(len) {
      return randomColor({
        count: len,
        luminosity: 'bright',
        hue: 'random',
      });
    },
    getDismissalChart(data, label = 'Число увольнений') {
      let colors = this.getColors(data.length),
        chartInfo = {
          labels: [],
          datasets: [
            {
              label: label,
              backgroundColor: colors,
              borderColor: colors,
              borderWidth: 1,
              data: [],
            },
          ],
        };
      for (let i in data) {
        chartInfo.labels.push(data[i].key);
        chartInfo.datasets[0].data.push(data[i].value);
      }
      return chartInfo;
    },
    toast(message, color) {
      this.$vaToast.init({
        message: message,
        color: color,
        position: 'bottom-right',
      });
    },
  },
  computed: {
    user() {
      return this.userState.state.user;
    },
    departmentChart() {
      if (this.dismissals?.departmentChart) {
        return this.getDismissalChart(this.dismissals.departmentChart, 'Число увольнений в зависимости от отдела');
      } else return null;
    },
    positionChart() {
      if (this.dismissals?.positionChart) {
        return this.getDismissalChart(this.dismissals.positionChart, 'Число увольнений в зависимости от должности');
      } else return null;
    },
    competenceChart() {
      if (this.dismissals?.competenceChart) {
        return this.getDismissalChart(this.dismissals.competenceChart, 'Число увольнений в зависимости от компетенции');
      } else return null;
    },
    gradeChart() {
      if (this.dismissals?.gradeChart) {
        return this.getDismissalChart(this.dismissals.gradeChart, 'Число увольнений в зависимости от грейда');
      } else return null;
    },
    genderChart() {
      if (this.dismissals?.genderChart) {
        return this.getDismissalChart(this.dismissals.genderChart);
      } else return null;
    },
    workExpChart() {
      if (this.dismissals?.workExpChart) {
        return this.getDismissalChart(this.dismissals.workExpChart, 'Число увольнений в зависимости от стажа работы');
      } else return null;
    },
    predictionChart() {
      if (this.dismissals?.predictions) {
        return this.getDismissalChart(this.dismissals.predictions, 'Прогноз числа увольнений');
      }
      return null;
    },
  },
};
</script>

<style scoped>
@import 'DismissalPage.scss';
</style>
