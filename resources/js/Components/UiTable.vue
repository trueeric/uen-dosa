<template>
  <div class="ui-table">
    <!-- 加載中狀態 -->
    <div v-if="loading" class="ui-table-loading">加載中...</div>

    <!-- 表格 -->
    <table v-else>
      <thead>
        <tr>
          <th
            v-for="column in columns"
            :key="column.key"
            :style="{ width: column.width || 'auto' }"
          >
            {{ column.title }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in data" :key="row.id">
          <td v-for="column in columns" :key="column.key">
            <!-- 插槽支持 -->
            <slot :name="column.key" :row="row">{{ row[column.key] }}</slot>
          </td>
        </tr>
        <tr v-if="data.length === 0">
          <td :colspan="columns.length" class="text-center">暫無數據</td>
        </tr>
      </tbody>
    </table>

    <!-- 分頁 -->
    <div v-if="pagination" class="ui-table-pagination">
      <el-pagination
        v-model:current-page="pagination.currentPage"
        :page-size="pagination.pageSize"
        :total="pagination.total"
        layout="prev, pager, next"
        @current-change="handlePageChange"
      />
    </div>
  </div>
</template>

<script setup>
defineProps({
  columns: {
    type: Array,
    required: true,
    default: () => [],
  },
  data: {
    type: Array,
    required: true,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  pagination: {
    type: Object,
    default: null,
  },
});

defineEmits(["page-change"]);

const handlePageChange = (page) => {
  emit("page-change", page);
};
</script>

<style scoped>
.ui-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1rem;
}

.ui-table th,
.ui-table td {
  padding: 8px;
  border: 1px solid #ddd;
  /* text-align: left; */
}

.ui-table th {
  background-color: #f9f9f9;
}

.ui-table-loading {
  text-align: center;
  padding: 1rem;
}

.ui-table-pagination {
  display: flex;
  justify-content: flex-end;
  margin-top: 1rem;
}
</style>
