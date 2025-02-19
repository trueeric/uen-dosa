<!-- pages/UenScoreRecords/index.vue -->
<template>
  <div class="container mx-auto p-4">
    <div class="mb-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold">成績記錄管理</h1>
      <div class="flex gap-2">
        <UiButton @click="refreshData">
          <template #icon>
            <IconRefresh class="w-4 h-4" />
          </template>
          重新整理
        </UiButton>
        <UiButton variant="primary" @click="handleCreate">
          <template #icon>
            <IconPlus class="w-4 h-4" />
          </template>
          新增記錄
        </UiButton>
      </div>
    </div>

    <!-- 搜尋區塊 -->
    <div class="bg-white p-4 rounded-lg shadow mb-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <UiInput
          v-model="searchForm.target_no"
          placeholder="搜尋班級"
          clearable
        >
          <template #prefix>
            <IconSearch class="w-4 h-4" />
          </template>
        </UiInput>
        <UiSelect
          v-model="searchForm.semester"
          :options="semesterOptions"
          placeholder="選擇學期"
        />
        <UiDatePicker
          v-model="searchForm.date_range"
          type="daterange"
          placeholder="選擇日期範圍"
        />
        <UiButton variant="primary" @click="handleSearch">搜尋</UiButton>
      </div>
    </div>

    <!-- 資料表格 -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <UiTable
        :columns="columns"
        :data="records"
        :loading="loading"
        :pagination="pagination"
        @page-change="handlePageChange"
      >
        <!-- 班級欄位 -->
        <template #target_no="{ row }">
          <div class="flex items-center">
            <span>{{ row.target_no }}</span>
            <span class="ml-2 text-gray-500">{{ row.target_name }}</span>
          </div>
        </template>

        <!-- 扣分項目 -->
        <template #score_info="{ row }">
          <div>
            <div>{{ row.name }}</div>
            <div class="text-red-500">{{ row.points }} 分</div>
          </div>
        </template>

        <!-- 建立時間 -->
        <template #created_at="{ row }">
          {{ formatDate(row.created_at) }}
        </template>

        <!-- 操作欄位 -->
        <template #actions="{ row }">
          <div class="flex gap-2">
            <UiButton size="sm" @click="handleEdit(row)">編輯</UiButton>
            <UiButton size="sm" variant="danger" @click="handleDelete(row)"
              >刪除</UiButton
            >
          </div>
        </template>
      </UiTable>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useMessage } from "@/composables/useMessage";
import { formatDate } from "@/utils/date";

// 表格欄位定義
const columns = [
  {
    title: "班級",
    key: "target_no",
    slot: true,
  },
  {
    title: "扣分項目",
    key: "score_info",
    slot: true,
  },
  {
    title: "次數",
    key: "times",
    width: 100,
  },
  {
    title: "學期",
    key: "semester",
    width: 120,
  },
  {
    title: "建立時間",
    key: "created_at",
    slot: true,
    width: 150,
  },
  {
    title: "操作",
    key: "actions",
    slot: true,
    width: 150,
    fixed: "right",
  },
];

// 搜尋表單
const searchForm = reactive({
  target_no: "",
  semester: "",
  date_range: [],
});

// 分頁設定
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
});

// 資料相關
const records = ref([]);
const loading = ref(false);

// 學期選項
const semesterOptions = [
  { label: "113-1", value: "113-1" },
  { label: "112-2", value: "112-2" },
  { label: "112-1", value: "112-1" },
];

// 取得資料
const fetchData = async () => {
  loading.value = true;
  try {
    const params = {
      page: pagination.current,
      per_page: pagination.pageSize,
      target_no: searchForm.target_no,
      semester: searchForm.semester,
      start_date: searchForm.date_range[0],
      end_date: searchForm.date_range[1],
    };

    const response = await fetch("/api/uen-score-records", { params });
    const { data, meta } = await response.json();

    records.value = data;
    pagination.total = meta.total;
  } catch (error) {
    useMessage().error("取得資料失敗");
  } finally {
    loading.value = false;
  }
};

// 事件處理
const handleSearch = () => {
  pagination.current = 1;
  fetchData();
};

const handlePageChange = (page) => {
  pagination.current = page;
  fetchData();
};

const refreshData = () => {
  fetchData();
};

const handleCreate = () => {
  // 導航到新增頁面
  navigateTo("/uen-score-record/create");
};

const handleEdit = (row) => {
  // 導航到編輯頁面
  navigateTo(`/uen-score-record/${row.id}/edit`);
};

const handleDelete = async (row) => {
  if (!confirm("確定要刪除此記錄嗎？")) return;

  try {
    await fetch(`/api/uen-score-records/${row.id}`, {
      method: "DELETE",
    });
    useMessage().success("刪除成功");
    fetchData();
  } catch (error) {
    useMessage().error("刪除失敗");
  }
};

// 初始化
onMounted(() => {
  fetchData();
});
</script>
