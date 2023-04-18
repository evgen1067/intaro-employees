<template>
  <font-awesome-icon
    v-if="sort.key !== columnKey || (sort.key === columnKey && sort.value === null)"
    @click="sortChange(columnKey, 'asc')"
    class="pl-2 pr-2 cursor-pointer"
    icon="sort"
  />
  <template v-else>
    <font-awesome-icon
      v-if="sort.key === columnKey && sort.value === 'asc'"
      @click="sortChange(columnKey, 'desc')"
      class="pl-2 pr-2 cursor-pointer"
      icon="sort-asc"
    />
    <font-awesome-icon v-else @click="sortChange(columnKey, null)" class="pl-2 pr-2 cursor-pointer" icon="sort-desc" />
  </template>
</template>

<script>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

export default {
  name: 'SortIcon',
  components: { FontAwesomeIcon },
  emits: ['sortChange'],
  props: {
    sort: {
      type: Object,
      required: true,
    },
    columnKey: {
      type: String,
      required: true,
    },
  },
  methods: {
    sortChange(columnKey, value) {
      this.$emit('sortChange', {
        key: columnKey,
        value: value,
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>
