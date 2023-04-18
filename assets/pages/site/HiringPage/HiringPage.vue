<template>
  <template v-if="!loading">
    <div class="mr-3 mb-2">
      <va-select
        v-model="pagination.limit"
        :label="`Показать записи (${pagination.limit})`"
        :options="pagination.options"
        class="mr-3 mb-2"
        color="#702cf9"
      />
    </div>
    <va-data-table
      :animated="true"
      :clickable="true"
      :columns="hiringInformation.columns"
      :items="list"
      :striped="true"
      :item-size="settings.itemsSize"
      :wrapper-size="settings.wrapperSize"
      selected-color="#702cf9"
      sticky-header
      virtual-scroller
      @row:dblclick="handleDblClick"
    >
      <template #headerAppend>
        <tr>
          <th v-for="(col, key) in hiringInformation.columns" :key="key">
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
        <tr>
          <th v-for="key in Object.keys(createdItem)" :key="key">
            <va-select
              v-if="filter.filter[key].type === 'list'"
              v-model="createdItem[key]"
              class="w-100 p-1"
              :outline="true"
              color="#4056A1"
              :options="filter.filter[key].listItems"
              text-by="label"
              value-by="listValueId"
              clearable
              searchable
              max-height="150px"
              search-placeholder-text="Поиск"
            />
            <va-input
              v-else
              v-model="createdItem[key]"
              class="w-100 p-1"
              :outline="true"
              style="--va-input-bordered-color: #702cf9"
              :placeholder="hiringInformation.columns.find(x => x.key === key).label"
            />
          </th>
          <th class="p-1 d-flex">
            <va-button :disabled="!isNewData" block @click="addNewItem"> Добавить запись </va-button>
          </th>
        </tr>
      </template>
      <template #cell(actions)="{ rowIndex }">
        <va-button preset="plain" icon="delete" @click="deleteItemById(rowIndex)" />
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
    <va-modal
      class="modal-crud-example"
      :model-value="!!editedItem"
      title="Обновление"
      size="small"
      @ok="editItem"
      @cancel="resetEditedItem"
    >
      <template v-for="key in Object.keys(editedItem)" :key="key">
        <template v-if="key !== 'id'">
          <va-select
            v-if="filter?.filter[key]?.type === 'list'"
            v-model="editedItem[key]"
            class="w-100 p-1"
            :outline="true"
            color="#4056A1"
            :options="filter.filter[key].listItems"
            text-by="label"
            value-by="listValueId"
            clearable
            searchable
            max-height="150px"
            search-placeholder-text="Поиск"
          />
          <va-input
            v-else
            v-model="editedItem[key]"
            class="w-100 p-1"
            :outline="true"
            style="--va-input-bordered-color: #702cf9"
            :placeholder="hiringInformation.columns.find(x => x.key === key).label"
          />
        </template>
      </template>
    </va-modal>
  </template>
  <load-spinner v-else></load-spinner>
</template>

<script>
import { userSymbol } from '../../../store';
import LoadSpinner from '../../../ui/spinner/LoadSpinner.vue';
import { HiringApi } from '../../../api';
import { getHiringInformation } from '../../../helpers/hiring';
import { VaDataTable, VaInput, VaModal, VaSelect } from 'vuestic-ui';
import EmployeeFilter from '../EmployeePage/components/EmployeeFilter/EmployeeFilter.vue';
import { cloneDeep } from 'lodash';

export default {
  name: 'HiringPage',
  components: { VaSelect, VaModal, VaInput, EmployeeFilter, VaDataTable, LoadSpinner },
  inject: {
    userState: {
      from: userSymbol,
    },
  },
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
  data: () => ({
    loading: true,
    hiringInformation: null,
    list: null,
    totalCount: 0,
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
    createdItem: null,
    editedItemId: null,
    editedItem: null,
  }),
  async created() {
    const result = await HiringApi.getManagers(this.user.token);
    if (result.status) {
      const currentId = this.user.id;
      this.hiringInformation = getHiringInformation(result.data, currentId);
      this.filter.filter = this.hiringInformation.filter;
      this.createdItem = { ...this.hiringInformation.defaultItem };
      await this.fetchData();
    } else {
      // TODO ERROR 500
    }
  },
  methods: {
    async fetchData() {
      this.loading = true;
      const result = await HiringApi.getList(this.user.token, {
        filter: JSON.stringify(this.clearFilter()),
        sort: this.filter.sort.value ? JSON.stringify(this.filter.sort) : null,
        page: this.pagination.page,
        limit: this.pagination.limit,
      });
      if (result.status) {
        this.list = result.data;
        this.totalCount = result.totalCount;
      } else {
        this.toast(result.data, 'danger');
      }
      this.loading = false;
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
    async addNewItem() {
      this.loading = true;
      const result = (await HiringApi.addRecord(this.user.token, this.createdItem)).data;
      if (result.status) {
        this.toast(result.data, 'success');
        this.resetCreatedItem();
        await this.fetchData();
      } else {
        this.toast(result.data, 'danger');
      }
      this.loading = false;
    },
    async editItem() {
      this.loading = true;
      const result = (await HiringApi.editRecord(this.user.token, this.editedItemId, this.editedItem)).data;
      if (result.status) {
        this.toast(result.data, 'success');
        this.resetEditedItem();
        await this.fetchData();
      } else {
        this.toast(result.data, 'danger');
      }
      this.loading = false;
    },
    // TODO API
    async deleteItemById(id) {
      this.list = [...this.list.slice(0, id), ...this.list.slice(id + 1)];
    },
    async handleDblClick(e) {
      this.loading = true;
      this.editedItemId = e.item.id;
      const result = await HiringApi.getRecord(this.user.token, this.editedItemId);
      if (result.status) {
        this.editedItem = result.data;
      } else {
        this.toast(result.data, 'danger');
      }
      this.loading = false;
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
        } else if (filter[key]?.value === '') {
          delete filter[key];
        } else if (key.includes('date')) {
          filter[key].value = filter[key].value.toLocaleDateString('ru-RU');
        }
      }
      return filter;
    },
    resetCreatedItem() {
      this.createdItem = { ...this.hiringInformation.defaultItem };
    },
    resetEditedItem() {
      this.editedItem = null;
      this.editedItemId = null;
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
    pages() {
      return this.pagination.limit && this.pagination.limit !== 0
        ? Math.ceil(this.totalCount / this.pagination.limit)
        : this.totalCount;
    },
    isNewData() {
      return (
        [1, 2].includes(this.createdItem.status) &&
        this.createdItem.position.trim().length > 0 &&
        this.createdItem.expected_count >= 0 &&
        [1, 2, 3, 4, 5, 6].includes(this.createdItem.urgency) &&
        this.createdItem.director.trim().length > 0 &&
        this.createdItem.offers_count >= 0 &&
        this.createdItem.employees_count >= 0
      );
    },
  },
};
</script>

<style scoped>

</style>
