<script setup>
import { ref, reactive } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { Refresh, Plus, Search, Edit, Delete } from "@element-plus/icons-vue";
import UiTable from "@/components/UiTable.vue";
import { useDateFormatter } from "@/composables/useDateFormatter";

// 從 Inertia 的 props 獲取資料
const { props } = usePage();
const records = ref(props.records);
const pagination = reactive(props.pagination);
const weekOptions = ref(props.weekOptions);

// 日期格式化
const { formatDate } = useDateFormatter();

// 搜尋表單
const searchForm = reactive({
  target_no: "",
  week_no: "",
  date_range: [],
});

// 加載狀態
const loading = ref(false);

// 搜尋資料
const handleSearch = () => {
  loading.value = true;

  // 確保日期範圍存在且有效
  const [startDate, endDate] = searchForm.date_range || [null, null];

  console.log("搜尋條件：", {
    date_range: searchForm.date_range,
    startDate,
    endDate,
  });

  router.get(
    route("uen-score-records.index"),
    {
      page: 1,
      target_no: searchForm.target_no,
      week_no: searchForm.week_no,
      start_date: formatDate(startDate),
      end_date: formatDate(endDate),
    },
    {
      preserveState: true,
      preserveScroll: true, // 保持滾動位置
      onBefore: () => {
        loading.value = true;
      },
      onSuccess: () => {
        // 可以在這裡添加成功提示
        console.log("搜尋成功");
      },
      onError: (errors) => {
        console.error("搜尋錯誤：", errors);
      },
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
      week_no: searchForm.week_no,
      start_date: formatDate(searchForm.date_range[0])[0],
      end_date: formatDate(searchForm.date_range[1])[0],
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
  <!-- <pre>{{ weekOptions }}</pre> -->
  <div class="container mx-auto p-4">
    <div class="mb-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold">成績記錄管理</h1>
      <div class="flex gap-2">
        <el-button @click="handleSearch">
          <el-icon :size="20" :color="primaryColor"><Refresh /></el-icon>
          <span>重新整理 </span>
        </el-button>
        <el-button variant="primary" @click="handleCreate">
          <el-icon><Plus /></el-icon>
          新增記錄
        </el-button>
      </div>
    </div>

    <!-- 搜尋區塊 -->
    <div class="bg-white p-4 rounded-lg shadow mb-4">
      <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 portrait:grid-cols-2">
        <!-- 搜尋班級 -->
        <el-input
          v-model="searchForm.target_no"
          placeholder="搜尋班級"
          clearable
        >
          <template #prefix>
            <el-icon><Search /></el-icon>
          </template>
        </el-input>

        <!-- 選擇周別 -->
        <el-select
          v-model="searchForm.week_no"
          placeholder="選擇周別"
          clearable
          class="w-full"
        >
          <template #prefix>
            <el-icon><Search /></el-icon>
          </template>
          <el-option
            v-for="option in weekOptions"
            :key="option.value"
            :label="option.label"
            :value="option.value"
            placeholder="選擇周別"
          />
        </el-select>

        <!-- 日期範圍選擇 -->
        <el-date-picker
          v-model="searchForm.date_range"
          type="daterange"
          start-placeholder="選擇日期範圍"
          class="w-full"
        />

        <!-- 搜尋按鈕 -->
        <el-button type="primary" @click="handleSearch" class="w-full h-[40px]">
          搜尋
        </el-button>
      </div>
    </div>

    <!-- 資料表格 -->
    <!-- 資料表格 -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <el-table
        :data="records.data"
        v-loading="loading"
        style="width: 100%"
        border
      >
        <!-- 學期 -->
        <el-table-column prop="semester" label="學期" width="120" />

        <!-- 班級 -->
        <el-table-column prop="targetable.class_no" label="班級" />

        <!-- 評分項目 -->
        <el-table-column label="評分項目">
          <template #default="{ row }">
            <div>
              <div>{{ row.name }}</div>
              <div
                :class="row.points >= 0 ? 'text-indigo-500' : 'text-red-500'"
              >
                {{ row.points }}
              </div>
            </div>
          </template>
        </el-table-column>

        <!-- 次數 -->
        <el-table-column prop="times" label="次數" width="100" align="center" />

        <!-- 小計 -->
        <el-table-column prop="subtotal" label="小計" width="100" align="right">
          <template #default="{ row }">
            <span class="pr-4">{{ row.subtotal }}</span>
          </template>
        </el-table-column>

        <!-- 評分日期 -->
        <el-table-column label="評分日期" width="150" align="center">
          <template #default="{ row }">
            {{ formatDate(row.score_date)[3] }}
          </template>
        </el-table-column>

        <!-- 周別 -->
        <el-table-column prop="week_no" label="周別" width="150" align="right">
          <template #default="{ row }">
            <span class="pr-4">{{ row.week_no }}</span>
          </template>
        </el-table-column>

        <!-- 操作 -->
        <el-table-column label="操作" width="150" fixed="right" align="center">
          <template #default="{ row }">
            <div class="flex gap-1 justify-center">
              <el-button type="warning" size="small" @click="handleEdit(row)">
                <el-icon><Edit /></el-icon>
                編輯
              </el-button>
              <el-button type="danger" size="small" @click="handleDelete(row)">
                <el-icon><Delete /></el-icon>
                刪除
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分頁 -->
      <div class="flex justify-end p-4">
        <el-pagination
          v-model:current-page="pagination.current_page"
          v-model:page-size="pagination.per_page"
          :total="pagination.total"
          :page-sizes="[10, 20, 30, 50]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </div>
  </div>
</template>
