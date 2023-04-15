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
        <div class="col-2">
          <va-select
            v-model="filter.department"
            label="Отдел"
            color="#4056A1"
            class="w-100"
            :options="departments"
            text-by="label"
            value-by="listValueId"
            clearable
            searchable
            max-height="150px"
            search-placeholder-text="Поиск"
          />
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-2">
          <info-card :value="turnovers.totalNumber" description="число сотрудников" color="#4056A1" class="h-100" />
        </div>
        <div class="col-2">
          <info-card :value="turnovers.acceptedNumber" description="принято" color="success" class="h-100" />
        </div>
        <div class="col-2">
          <info-card :value="turnovers.dismissedNumber" description="уволено" color="#F13C20" class="h-100" />
        </div>
        <div class="col-2">
          <info-card :value="turnovers.averageNumber" description="Среднеспис. числ." color="#30B5C8" class="h-100" />
        </div>
        <div class="col-2">
          <info-card
            :value="turnovers.turnoverRate"
            description="Текучесть"
            color="#F37A48"
            class="h-100"
          />
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
              <app-chart :data="predictionsChart" type="line" />
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
import { LoadSpinner, InfoCard, AppChart } from '../../ui';
import { VaCard, VaCardContent, VaDateInput } from 'vuestic-ui';
import { AnalyticsApi, EmployeeApi } from '../../api';
import randomColor from 'randomcolor';
import { userSymbol } from '../../store';

export default {
  name: 'DismissalPage',
  components: { VaCard, VaCardContent, AppChart, InfoCard, VaDateInput, LoadSpinner },
  data: () => ({
    loading: false,
    filter: {
      from: '',
      to: '',
      department: '',
    },
    departments: [],
    turnovers: null,
  }),
  inject: {
    userState: {
      from: userSymbol,
    },
  },
  watch: {
    filter: {
      immediate: true,
      deep: true,
      async handler() {
        if (!this.loading) {
          this.loading = true;
          await this.fetchTurnoverData();
          this.loading = false;
        }
      },
    },
  },
  async created() {
    this.loading = true;
    await this.defaultFilter();
    await this.fetchTurnoverData();
    this.loading = false;
  },
  methods: {
    async defaultFilter() {
      this.filter.from = new Date();
      this.filter.from = this.filter.from.setMonth(this.filter.from.getMonth() - 12);
      this.filter.from = new Date(this.filter.from);
      this.filter.to = new Date();
      const result = await EmployeeApi.getDepartments(this.user.token);
      this.departments = result.data;
    },
    async fetchTurnoverData() {
      this.turnovers = await AnalyticsApi.getTurnover(this.user.token, this.clearFilter());
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
      if (this.filter.department) {
        return {
          valueFrom: this.filter.from.toLocaleDateString('ru-RU'),
          valueTo: this.filter.to.toLocaleDateString('ru-RU'),
          department: this.filter.department,
        };
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
    getDismissalChart(data, label = 'Коэффициент текучести') {
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
  },
  computed: {
    user() {
      return this.userState.state.user;
    },
    genderChart() {
      if (this.turnovers?.genderChart) {
        return this.getDismissalChart(this.turnovers.genderChart);
      } else return null;
    },
    workExpChart() {
      if (this.turnovers?.workExperienceChart) {
        return this.getDismissalChart(
          this.turnovers.workExperienceChart,
          'Коэффициент текучести в зависимости от стажа работы',
        );
      } else return null;
    },
    departmentChart() {
      if (this.turnovers?.departmentChart) {
        return this.getDismissalChart(this.turnovers.departmentChart, 'Коэффициент текучести в зависимости от отдела');
      } else return null;
    },
    positionChart() {
      if (this.turnovers?.positionChart) {
        return this.getDismissalChart(this.turnovers.positionChart, 'Коэффициент текучести в зависимости от должности');
      } else return null;
    },
    competenceChart() {
      if (this.turnovers?.competenceChart) {
        return this.getDismissalChart(
          this.turnovers.competenceChart,
          'Коэффициент текучести в зависимости от компетенции',
        );
      } else return null;
    },
    gradeChart() {
      if (this.turnovers?.gradeChart) {
        return this.getDismissalChart(this.turnovers.gradeChart, 'Коэффициент текучести в зависимости от грейда');
      } else return null;
    },
    predictionsChart() {
      if (this.turnovers?.predictionsChart) {
        return this.getDismissalChart(this.turnovers.predictionsChart, 'Прогноз коэффициента текучести');
      } else return null;
    },
  },
};
</script>

<style scoped>
@import 'TurnoverPage.scss';
</style>
