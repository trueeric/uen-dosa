<script setup>
import { ref, reactive } from "vue";
import { usePage, router } from "@inertiajs/vue3";

// 從 Inertia 的 props 獲取資料
const { props } = usePage();
const records = ref(props.records.data);
const pagination = ref(props.records.meta);
const semesterOptions = ref(props.semesterOptions);
const filters = reactive(props.filters);

// 搜尋表單
const searchForm = reactive({
  target_no: filters.target_no || "",
  semester: filters.semester || "",
  start_date: filters.start_date || "",
  end_date: filters.end_date || "",
});

// 搜尋資料
const handleSearch = () => {
  router.get(route("uen-score-records.index"), searchForm, {
    preserveState: true,
  });
};

// 分頁切換
const handlePageChange = (page) => {
  router.get(
    route("uen-score-records.index"),
    {
      ...searchForm,
      page,
    },
    {
      preserveState: true,
    }
  );
};

// 重置搜尋表單
const resetFilters = () => {
  searchForm.target_no = "";
  searchForm.semester = "";
  searchForm.start_date = "";
  searchForm.end_date = "";
  handleSearch();
};
</script>

<template>
  <div class="container mx-auto p-4">
    <div class="mb-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold">成績記錄管理</h1>
      <div class="flex gap-2">
        <UiButton @click="resetFilters">重置</UiButton>
        <UiButton variant="primary" @click="handleSearch">搜尋</UiButton>
      </div>
    </div>

    <!-- 搜尋區塊 -->
    <div class="bg-white p-4 rounded-lg shadow mb-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <UiInput
          v-model="searchForm.target_no"
          placeholder="搜尋班級"
          clearable
        />
        <UiSelect
          v-model="searchForm.semester"
          :options="semesterOptions"
          placeholder="選擇學期"
        />
        <UiDatePicker v-model="searchForm.start_date" placeholder="開始日期" />
        <UiDatePicker v-model="searchForm.end_date" placeholder="結束日期" />
      </div>
    </div>

    <!-- 資料表格 -->
    <UiTable :data="records">
      <!-- 表格欄位 -->
    </UiTable>

    <!-- 分頁 -->
    <UiPagination
      :current="pagination.current_page"
      :total="pagination.total"
      :page-size="pagination.per_page"
      @change="handlePageChange"
    />
  </div>
</template>
