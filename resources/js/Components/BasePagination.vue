<script setup>
import { computed } from "vue";

const props = defineProps({
  pagination: {
    type: Object,
    required: true,
    default: () => ({
      current_page: 1,
      per_page: 10,
      total: 0,
    }),
  },
  pageSizes: {
    type: Array,
    default: () => [10, 20, 30, 50],
  },
});

const emit = defineEmits([
  "update:pagination",
  "size-change",
  "current-change",
]);

// 計算屬性，用於同步更新
const currentPage = computed({
  get: () => props.pagination.current_page,
  set: (val) =>
    emit("update:pagination", {
      ...props.pagination,
      current_page: Number(val),
    }),
});

const pageSize = computed({
  get: () => props.pagination.per_page,
  set: (val) =>
    emit("update:pagination", { ...props.pagination, per_page: Number(val) }),
});

// 處理分頁大小變化
const handleSizeChange = (val) => {
  emit("size-change", Number(val));
};

// 處理當前頁變化
const handleCurrentChange = (val) => {
  emit("current-change", Number(val));
};
</script>

<template>
  <div class="flex justify-end p-4">
    <el-pagination
      v-model:current-page="currentPage"
      v-model:page-size="pageSize"
      :total="pagination.total"
      :page-sizes="pageSizes"
      :pager-count="7"
      :background="true"
      layout="total, sizes, prev, pager, next, jumper"
      @size-change="handleSizeChange"
      @current-change="handleCurrentChange"
    />
  </div>
</template>
