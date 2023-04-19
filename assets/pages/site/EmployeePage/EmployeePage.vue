<template>
  <template v-if="!loading">
    <div class="d-flex align-content-center">
      <div class="mr-3 mb-2">
        <va-select
          v-model="pagination.limit"
          :label="`Показать записи (${pagination.limit})`"
          :options="pagination.options"
          class="mr-3 mb-2"
          color="#702cf9"
        />
      </div>
      <div class="mr-3 mb-2">
        <va-select
          v-model="columns.selected"
          :options="columns.options"
          :max-visible-options="1"
          label="Показать атрибуты"
          class="mr-3 mb-2"
          text-by="label"
          color="#702cf9"
          multiple
          selected-top-shown
          @update:modelValue="changeSelectedColumns"
        />
      </div>
    </div>
    <template v-if="!tableLoading">
      <va-data-table
        :animated="true"
        :clickable="true"
        :columns="columns.selected"
        :item-size="settings.itemsSize"
        :wrapper-size="settings.wrapperSize"
        :items="table.items.selected"
        :striped="true"
        selected-color="#702cf9"
        sticky-header
        virtual-scroller
      >
        <template #headerAppend>
          <tr>
            <td v-for="(col, key) in columns.selected" :key="key">
              <employee-filter
                :sort="filter.sort"
                :column-filter="filter.filter[col.key]"
                :column="col"
                @sortChange="sortChange"
                @search="search"
                @changeFilter="changeFilter"
              />
            </td>
          </tr>
        </template>
      </va-data-table>
      <div class="table-example--pagination">
        <va-pagination v-model="pagination.page" input :pages="pages">
          <template #prevPageLink="{ onClick, disabled }">
            <va-button color="#702cf9" :disabled="disabled" aria-label="go prev page" outline @click="onClick">
              Назад
            </va-button>
          </template>
          <template #nextPageLink="{ onClick, disabled }">
            <va-button color="#702cf9" :disabled="disabled" aria-label="go next page" outline @click="onClick">
              Далее
            </va-button>
          </template>
        </va-pagination>
      </div>
    </template>
    <load-spinner style="min-height: 75vh" v-else />
  </template>
  <load-spinner style="min-height: 75vh" v-else />
</template>

<script>
import { LoadSpinner } from '../../../ui';
import { EmployeeApi } from '../../../api';
import { getEmployeeInformation } from '../../../helpers/employee';
import { cloneDeep } from 'lodash';
import EmployeeFilter from './components/EmployeeFilter/EmployeeFilter.vue';
import { VaButton, VaDataTable, VaPagination } from 'vuestic-ui';
import { userSymbol } from '../../../store';
import { errorRoute } from '../../../helpers/constants';

export default {
  name: 'EmployeePage',
  components: { VaDataTable, VaButton, VaPagination, EmployeeFilter, LoadSpinner },
  inject: {
    userState: {
      from: userSymbol,
    },
  },
  data: () => ({
    loading: true,
    pagination: {
      page: 1,
      limit: 20,
      options: [20, 50, 100, 'Все'],
    },
    columns: {
      selected: [],
      options: [],
    },
    filter: {
      filter: {},
      sort: {
        key: 'name',
        value: null,
      },
    },
    table: {
      total: 0,
      items: {
        selected: [],
        options: [],
      },
    },
    settings: {
      wrapperSize: 750,
      itemsSize: 46,
    },
    employee: {},
    tableLoading: false,
  }),
  async created() {
    const result = await Promise.all([
      EmployeeApi.getCompanies(this.user.token),
      EmployeeApi.getDepartments(this.user.token),
      EmployeeApi.getPositions(this.user.token),
      EmployeeApi.getCompetences(this.user.token),
      EmployeeApi.getGrades(this.user.token),
    ]);
    if (!result[0].status || !result[1].status || !result[2].status || !result[3].status || !result[4].status) {
      this.$router.push({ name: errorRoute.name });
    }
    this.employee = getEmployeeInformation(
      result[0].data,
      result[1].data,
      result[2].data,
      result[3].data,
      result[4].data,
    );
    this.filter.filter = this.employee.filter;
    this.columns.selected = this.employee.columns;
    this.columns.options = this.employee.columns;
    await this.fetchTableData();
    this.loading = false;
  },
  watch: {
    pagination: {
      immediate: true,
      deep: true,
      async handler() {
        if (!this.loading) {
          await this.fetchTableData();
        }
      },
    },
  },
  methods: {
    async changeSelectedColumns() {
      this.tableLoading = true;
      await this.mapTableData();
      this.tableLoading = false;
    },
    async fetchTableData() {
      this.tableLoading = true;
      const result = await EmployeeApi.getEmployees(this.user.token, {
        filter: JSON.stringify(this.clearFilter()),
        sort: this.filter.sort.value ? JSON.stringify(this.filter.sort) : null,
        page: this.pagination.page,
        limit: this.pagination.limit,
      });
      if (!result.status) {
        this.toast(result.data, 'danger');
        this.$router.push({ name: errorRoute.name });
      } else {
        this.toast('Данные загружены', 'success');
        this.table.total = result.totalCount;
        this.table.items.options = result.data;
        await this.mapTableData();
        this.tableLoading = false;
      }
    },
    async changeFilter(e) {
      this.filter.filter[e.key] = cloneDeep(e.filter);
      await this.fetchTableData();
    },
    search(e) {
      this.filter.filter[e.key] = e.filter;
      this.fetchTableData();
    },
    async sortChange(e) {
      this.filter.sort.key = e.key;
      this.filter.sort.value = e.value;
      await this.fetchTableData();
    },
    clearFilter() {
      let filter = cloneDeep(this.filter.filter);
      for (let key in filter) {
        delete filter[key].iconName;
        delete filter[key].label;
        delete filter[key]?.listItems;
        if (filter[key]?.type === 'number_inequality') {
          if (filter[key]?.valueFrom === '' || !filter[key]?.valueFrom) {
            delete filter[key]?.valueFrom;
          }
          if (filter[key]?.valueTo === '' || !filter[key]?.valueTo) {
            delete filter[key]?.valueTo;
          }
          if (!filter[key]?.valueFrom && !filter[key]?.valueTo) {
            delete filter[key];
          }
        } else if (filter[key]?.value === '' || !filter[key]?.value) {
          delete filter[key];
        } else if (key.includes('date')) {
          filter[key].value = filter[key].value.toLocaleDateString('ru-RU');
        }
      }
      return filter;
    },
    async mapTableData() {
      let selectedKeys = [];
      this.table.items.selected = [];
      this.columns.selected.forEach(c => {
        selectedKeys.push(c.key);
      });
      this.columns.selected = [];
      this.columns.options.forEach(c => {
        if (selectedKeys.includes(c.key)) {
          this.columns.selected.push(c);
        }
      });
      this.table.items.options.forEach(row => {
        let object = {};
        selectedKeys.forEach(key => {
          object[key] = row[key];
        });
        this.table.items.selected.push(object);
      });
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
    pages() {
      return this.pagination.limit && this.pagination.limit !== 0
        ? Math.ceil(this.table.total / this.pagination.limit)
        : this.table.total;
    },
    user() {
      return this.userState.state.user;
    },
  },
};
</script>

<style lang="scss">
@import 'EmployeePage';
</style>
