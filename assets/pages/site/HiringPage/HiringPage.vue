<template>
  <template v-if="!loading">
    <va-modal ref="hiringModal" v-if="request.form && request.form.length" v-model="modal.show">
      <template #content>
        <div class="d-flex flex-column">
          <template v-if="!cardLoading">
            <va-content class="content">
              <h4>{{ modal.title }}</h4>
            </va-content>
            <va-form ref="form" tag="form" @validation="request.validation = $event" @submit.prevent="submitForm">
              <template v-for="(inpInfo, key) in request.form" :key="key">
                <va-select
                  v-if="inpInfo.datatype === 'list'"
                  v-model="request.data[inpInfo.key]"
                  :label="inpInfo.label"
                  :options="inpInfo.listItems"
                  :rules="inpInfo?.rule ? inpInfo?.rule : []"
                  text-by="label"
                  value-by="listValueId"
                  class="w-100 mb-3"
                  clearable
                  color="#702cf9"
                ></va-select>
                <va-input
                  v-else
                  v-model="request.data[inpInfo.key]"
                  :label="inpInfo.label"
                  :rules="inpInfo?.rule ? inpInfo?.rule : []"
                  class="w-100 mb-3"
                  type="text"
                ></va-input>
              </template>
              <va-button type="submit" class="w-100 mb-3"> {{ modal.btn }} </va-button>
            </va-form>
          </template>
          <load-spinner v-else />
        </div>
      </template>
    </va-modal>
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
        <va-button color="#702cf9" gradient icon="person_add" @click="addRow">Добавить</va-button>
      </div>
      <div class="mr-3 mb-2">
        <va-button color="#F13C20" gradient icon="clear" @click="deleteRows">Удалить</va-button>
      </div>
    </div>
    <va-data-table
      :animated="true"
      :clickable="true"
      :columns="table.columns"
      :items="table.items"
      :striped="true"
      :item-size="settings.itemsSize"
      :wrapper-size="settings.wrapperSize"
      :selectable="true"
      items-track-by="id"
      selected-color="#702cf9"
      sticky-header
      virtual-scroller
      @selectionChange="table.selectedItems = $event.currentSelectedItems"
      @row:dblclick="editRow"
    >
      <template #headerAppend>
        <tr>
          <th></th>
          <th v-for="(col, key) in table.columns" :key="key">
            <employee-filter
              v-if="col.key !== 'actions'"
              :sort="filter.sort"
              :column-filter="filter.filter[col.key]"
              :column="col"
              @sortChange="sortChange"
              @search="search"
              @changeFilter="changeFilter"
            />
          </th>
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
  <load-spinner v-else />
</template>

<script>
import { userSymbol } from '../../../store';
import { VaButton, VaContent, VaDataTable, VaForm, VaInput, VaModal, VaPagination, VaSelect } from 'vuestic-ui';
import EmployeeFilter from '../EmployeePage/components/EmployeeFilter/EmployeeFilter.vue';
import { LoadSpinner } from '../../../ui';
import { cloneDeep } from 'lodash';
import { HiringApi } from '../../../api';
import { getHiringInformation } from '../../../helpers/hiring';
export default {
  name: 'HiringPage',
  inject: {
    userState: {
      from: userSymbol,
    },
  },
  components: {
    VaDataTable,
    VaPagination,
    VaButton,
    VaInput,
    VaForm,
    VaContent,
    VaModal,
    EmployeeFilter,
    VaSelect,
    LoadSpinner,
  },
  data: () => ({
    loading: true,
    cardLoading: false,
    settings: {
      wrapperSize: 550,
      itemsSize: 46,
    },
    pagination: {
      page: 1,
      limit: 20,
      options: [20, 50, 100, 'Все'],
    },
    filter: {
      filter: {},
      sort: {
        key: 'status',
        value: null,
      },
    },
    table: {
      items: null,
      total: null,
      columns: null,
      selectedItems: null,
    },
    request: {
      default: null,
      form: null,
      data: null,
      add: true,
      validation: null,
    },
    modal: {
      title: '',
      btn: '',
      show: false,
    },
  }),
  watch: {
    pagination: {
      immediate: true,
      deep: true,
      async handler() {
        if (!this.loading) {
          await this.fetchData();
        }
      },
    },
  },
  async created() {
    const result = await HiringApi.getManagers(this.userToken);
    if (result.status) {
      const currentId = this.userId;
      const hiringPlan = getHiringInformation(result.data, currentId);
      this.filter.filter = hiringPlan.filter;
      this.request.default = hiringPlan.defaultItem;
      this.request.default.manager_name = currentId;
      this.request.default.status = 2;
      this.request.form = hiringPlan.dataInfo;
      this.table.columns = hiringPlan.columns;
      await this.fetchData();
    } else {
      // TODO ERROR 500
    }
  },
  methods: {
    toast(message, color) {
      this.$vaToast.init({
        message: message,
        color: color,
        position: 'bottom-right',
      });
    },
    clearFilter() {
      let filter = cloneDeep(this.filter?.filter);
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
    async sortChange(e) {
      this.filter.sort.key = e.key;
      this.filter.sort.value = e.value;
      await this.fetchData();
    },
    async changeFilter(e) {
      this.filter.filter[e.key] = cloneDeep(e.filter);
      await this.fetchData();
    },
    async search(e) {
      this.filter.filter[e.key] = e.filter;
      await this.fetchData();
    },
    async fetchData() {
      this.loading = true;
      const result = await HiringApi.getList(this.userToken, {
        filter: JSON.stringify(this.clearFilter()),
        sort: this.filter.sort.value ? JSON.stringify(this.filter.sort) : null,
        page: this.pagination.page,
        limit: this.pagination.limit,
      });
      if (result.status) {
        this.table.items = result.data;
        this.table.total = result.totalCount;
      } else {
        this.toast(result.data, 'danger');
      }
      this.loading = false;
    },
    addRow() {
      this.loading = true;
      this.request.add = true;
      this.modal.title = 'Создание записи';
      this.modal.btn = 'Создать';
      console.log(this.request.default);
      this.request.data = cloneDeep(this.request.default);
      this.loading = false;
      this.$nextTick(() => {
        this.modal.show = true;
      });
      this.loading = false;
    },
    async editRow(e) {
      this.loading = true;
      this.request.add = false;
      this.modal.title = 'Обновление записи';
      this.modal.btn = 'Обновить';
      const result = await HiringApi.getRecord(this.userToken, e.item.id);
      if (result.status) {
        this.request.data = result.data;
        this.$nextTick(() => {
          this.modal.show = true;
        });
      } else {
        this.toast(result.data, 'danger');
      }
      this.loading = false;
    },
    async submitForm() {
      this.request.validation = this.$refs.form.validate();
      if (this.request.validation) {
        this.cardLoading = true;
        if (this.request.add) {
          const result = (await HiringApi.addRecord(this.userToken, this.request.data)).data;
          this.toast(result.data, result.status ? 'success' : 'danger');
          if (result.status) {
            this.modal.show = false;
            await this.fetchData();
          }
        } else {
          const result = (await HiringApi.editRecord(this.userToken, this.request.data.id, this.request.data)).data;
          this.toast(result.data, result.status ? 'success' : 'danger');
          if (result.status) {
            this.modal.show = false;
            await this.fetchData();
          }
        }
        this.cardLoading = false;
      }
    },
    async deleteRows() {
      if (this.table.selectedItems && this.table.selectedItems.length) {
        if (confirm('Вы уверены, что желаете удалить выбранные записи?')) {
          this.loading = true;
          const result = (await HiringApi.deleteRecords(this.userToken, this.table.selectedItems)).data;
          await this.fetchData();
          this.loading = false;
          this.toast(result.data, result.status ? 'success' : 'danger');
        }
      } else {
        this.toast('Вы не выбрали ни одной записи', 'danger');
      }
    },
  },
  computed: {
    userToken() {
      return this.userState.state?.user?.token;
    },
    userId() {
      return this.userState.state?.user?.id;
    },
    pages() {
      return this.pagination.limit && this.pagination.limit !== 0
        ? Math.ceil(this.table.total / this.pagination.limit)
        : this.table.total;
    },
  },
};
</script>

<style lang="scss">
@import 'HiringPage';
</style>
