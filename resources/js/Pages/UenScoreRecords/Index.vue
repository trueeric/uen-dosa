<script setup>
import { ref, reactive } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { Refresh, Plus, Search } from "@element-plus/icons-vue";
import UiTable from "@/components/UiTable.vue";
import { useDateFormatter } from "@/composables/useDateFormatter";

// 從 Inertia 的 props 獲取資料
const { props } = usePage();
const records = ref(props.records);
const pagination = reactive(props.pagination);
const semesterOptions = ref(props.semesterOptions);

// 日期格式化
const { formatDate } = useDateFormatter();

// 搜尋表單
const searchForm = reactive({
  target_no: "",
  semester: "",
  date_range: [],
});

// 加載狀態
const loading = ref(false);

// 搜尋資料
const handleSearch = () => {
  loading.value = true;
  router.get(
    route("uen-score-records.index"),
    {
      page: 1,
      target_no: searchForm.target_no,
      semester: searchForm.semester,
      start_date: searchForm.date_range[0],
      end_date: searchForm.date_range[1],
    },
    {
      preserveState: true,
      onFinish: () => {
        loading.value = false;
      },
    }
  );
};

// 分頁切換
const handlePageChange = (page) => {
  loading.value = true;
  router.get(
    route("uen-score-records.index"),
    {
      page,
      target_no: searchForm.target_no,
      semester: searchForm.semester,
      start_date: searchForm.date_range[0],
      end_date: searchForm.date_range[1],
    },
    {
      preserveState: true,
      onFinish: () => {
        loading.value = false;
      },
    }
  );
};

// 新增記錄
const handleCreate = () => {
  router.visit(route("uen-score-records.create"));
};

// 編輯記錄
const handleEdit = (row) => {
  router.visit(route("uen-score-records.edit", { id: row.id }));
};

// 刪除記錄
const handleDelete = (row) => {
  if (!confirm("確定要刪除此記錄嗎？")) return;

  router.delete(route("uen-score-records.destroy", { id: row.id }), {
    onSuccess: () => {
      alert("刪除成功");
      handleSearch();
    },
    onError: () => {
      alert("刪除失敗");
    },
  });
};
</script>

<template>
  <!-- <pre>{{ records.data[0] }}</pre> -->
  <div class="container mx-auto p-4">
    <div class="mb-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold">成績記錄管理</h1>
      <div class="flex gap-2">
        <UiButton @click="handleSearch">
          <template #icon>
            <Refresh class="w-4 h-4" />
            <!-- <IconRefresh class="w-4 h-4" /> -->
          </template>
          重新整理
        </UiButton>
        <UiButton variant="primary" @click="handleCreate">
          <template #icon>
            <Plus class="w-4 h-4" />
            <!-- <IconPlus class="w-4 h-4" /> -->
          </template>
          新增記錄
        </UiButton>
      </div>
    </div>

    <!-- 搜尋區塊 -->
    <div class="bg-white p-4 rounded-lg shadow mb-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <el-input
          v-model="searchForm.target_no"
          placeholder="搜尋班級"
          clearable
        >
          <template #prefix>
            <Search class="w-4 h-4" />
            <!-- <IconSearch class="w-4 h-4" /> -->
          </template>
        </el-input>

        <el-select
          v-model="searchForm.semester"
          placeholder="選擇學期"
          clearable
        >
          <el-option
            v-for="option in semesterOptions"
            :key="option.value"
            :label="option.label"
            :value="option.value"
            placeholder="選擇學期"
          />
        </el-select>
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
        :columns="[
          { title: '學期', key: 'semester', width: 120 },
          { title: '班級', key: 'class_no', slot: true },
          { title: '評分項目', key: 'score_info', slot: true },
          { title: '次數', key: 'times', width: 100, align: 'right' },
          {
            title: '小計',
            key: 'subtotal',
            slot: true,
            width: '100px',
            align: 'right', // 內容置中
            headerAlign: 'center', // 標題置中
          },

          { title: '建立時間', key: 'created_at', slot: true, width: 150 },
          {
            title: '操作',
            key: 'actions',
            slot: true,
            width: 150,
            fixed: 'right',
          },
        ]"
        :data="records.data"
        :loading="loading"
        :pagination="pagination"
        @page-change="handlePageChange"
      >
        <!-- 學期欄位 -->
        <template #semester="{ row }">
          <div class="flex items-center">
            <span>{{ row.semester }}</span>
          </div>
        </template>
        >
        <!-- 班級欄位 -->
        <template #class_no="{ row }">
          <div class="flex items-center">
            <span>{{ row.targetable.class_no }}</span>
          </div>
        </template>

        <!-- 評分項目 -->
        <template #score_info="{ row }">
          <div>
            <div>{{ row.name }}</div>
            <div :class="row.points >= 0 ? 'text-indigo-500' : 'text-red-500'">
              {{ row.points }}
            </div>
          </div>
        </template>

        <!-- 次數 -->
        <template #times="{ row }">
          <div class="flex items-center">
            <span>{{ row.times }}</span>
          </div>
        </template>

        <!-- 自定義小計插槽 -->
        <template #subtotal="{ row }">
          <span>{{ row.subtotal }}</span>
          <!-- 計算小計 -->
        </template>

        <!-- 建立時間 -->
        <template #created_at="{ row }">
          <span>{{ formatDate(row.created_at)[2] }}</span>
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
