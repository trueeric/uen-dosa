<script setup>
  import { ref, reactive, computed, watch } from 'vue'
  import { router } from '@inertiajs/vue3'
  import { Search, Refresh } from '@element-plus/icons-vue'
  import { ElMessage } from 'element-plus'
  import BasePagination from '@/components/BasePagination.vue'

  const props = defineProps({
    records: {
      type: Object,
      default: () => ({
        data: [],
        meta: {
          current_page: 1,
          per_page: 10,
          total: 0,
        },
      }),
    },
    classes: {
      type: Array,
      default: () => [],
    },
    weeks: {
      type: Array,
      default: () => [],
    },
    filters: {
      type: Object,
      default: () => ({}),
    },
  })

  //* 除錯 預設為 false，需要時可以改為 true
  const debug = ref(false);
  // const debug = ref(true)

  // 加載狀態
  const loading = ref(false)

  // 將 records 改為響應式引用
  const records = ref(
    props.records || {
      data: [],
      meta: {
        current_page: 1,
        per_page: 10,
        total: 0,
      },
    },
  )

  // 添加導航函數
const navigateToDetail = (week_no, e_class) => {
  router.get(
    route('uen-score-records.index'),
    {
      week_no: week_no,
      target_no: e_class,
    },
    {
      preserveState: false,  // 不保留當前頁面狀態
      preserveScroll: false, // 不保留滾動位置
    }
  )
}

  // 分頁設置
  const pagination = reactive({
    current_page: Number(props.records?.meta?.current_page) || 1,
    per_page: Number(props.records?.meta?.per_page) || 10,
    total: Number(props.records?.meta?.total) || 0,
  })

  // 搜尋表單
  const searchForm = reactive({
    class: props.filters.class || '',
    week_no: props.filters.week_no || '',
  })

  // 計算是否有篩選條件
  const hasFilter = computed(() => {
    return !!searchForm.class || !!searchForm.week_no
  })

  // 處理分頁大小變化
  const handleSizeChange = (val) => {
    pagination.per_page = Number(val)
    pagination.current_page = 1 // 切換每頁顯示數量時重置為第一頁
    handleSearch()
  }

  // 處理當前頁變化
  const handleCurrentChange = (val) => {
    pagination.current_page = Number(val)
    handleSearch()
  }

  // 搜尋處理
  const handleSearch = () => {
    loading.value = true

    router.get(
      route('uen-score-type-sub-total.index'),
      {
        page: pagination.current_page,
        per_page: pagination.per_page,
        class: searchForm.class,
        week_no: searchForm.week_no,
      },
      {
        preserveState: true,
        preserveScroll: true,
        onBefore: () => {
          loading.value = true
        },
        onSuccess: (page) => {
          loading.value = false
          // 更新分頁信息
          if (page.props.records?.meta) {
            const meta = page.props.records.meta
            pagination.current_page = Number(meta.current_page)
            pagination.per_page = Number(meta.per_page)
            pagination.total = Number(meta.total)
          }
          ElMessage.success('搜尋成功')
        },
        onError: (errors) => {
          console.error('Search error:', errors)
          ElMessage.error('搜尋失敗：' + Object.values(errors).join(', '))
        },
        onFinish: () => {
          loading.value = false
        },
      },
    )
  }

  // 重置處理
  const handleReset = () => {
    searchForm.class = ''
    searchForm.week_no = ''
    pagination.current_page = 1
    pagination.per_page = 10
    handleSearch()
  }

  /// 修改 watch 來更新 records
  watch(
    () => props.records,
    (newRecords) => {
      if (newRecords) {
        records.value = newRecords
        if (newRecords.meta) {
          const meta = newRecords.meta
          pagination.current_page = Number(meta.current_page)
          pagination.per_page = Number(meta.per_page)
          pagination.total = Number(meta.total)
        }
      }
    },
    { immediate: true },
  )
</script>

<template>
  <div class="container mx-auto p-4">
    <!-- 如果需要偵錯資訊，可以加入這段 -->
    <div v-if="debug" class="bg-gray-100 p-4 mb-4 rounded">
      <h3 class="font-bold mb-2">Debug Information:</h3>

      <pre>分頁數據: {{ pagination }}</pre>
      <pre>Records Meta: {{ records?.meta }}</pre>
      <pre>Records : {{ records }}</pre>
      <!-- <pre>{{ records.data[0] }}</pre> -->
      <!-- <div v-for="record in records.data" :key="record.id" class="mb-2">
        <pre>uen_score_id:{{ record.id }}</pre>
        <pre>target_name:{{ record.computed_target_name }}</pre>
      </div> -->
    </div>
  </div>

  <div class="container mx-auto p-4">
    <div class="mb-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold">班級周別評分統計</h1>
      <div class="flex gap-2">
        <el-button type="primary" @click="handleSearch">
          <el-icon>
            <Search />
          </el-icon>
          搜尋
        </el-button>
        <el-button @click="handleReset">
          <el-icon>
            <Refresh />
          </el-icon>
          重置
        </el-button>
      </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-4">
      <div class="flex flex-wrap gap-4">
        <el-select v-model="searchForm.week_no" placeholder="選擇周別" clearable class="w-48">
          <el-option v-for="option in weeks" :key="option.value" :label="option.label" :value="option.value" />
        </el-select>

        <el-select v-model="searchForm.class" placeholder="選擇班級" clearable class="w-48">
          <el-option v-for="option in classes" :key="option.value" :label="option.label" :value="option.value" />
        </el-select>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow">
      <el-table :data="records.data" border style="width: 100%" v-loading="loading">
        <el-table-column prop="semester" label="學期" width="100" />
       <!-- 修改周別欄位為可點擊連結 -->
      <el-table-column label="周別" width="80">
        <template #default="{ row }">
          <a
            href="#"
            class="text-blue-600 hover:text-blue-800 hover:underline"
            @click.prevent="navigateToDetail(row.week_no, row.e_class)"
          >
            {{ row.week_no }}
          </a>
        </template>
      </el-table-column>

      <!-- 修改班級欄位為可點擊連結 -->
      <el-table-column label="班級" width="100">
        <template #default="{ row }">
          <a
            href="#"
            class="text-blue-600 hover:text-blue-800 hover:underline"
            @click.prevent="navigateToDetail(row.week_no, row.e_class)"
          >
            {{ row.e_class }}
          </a>
        </template>
      </el-table-column>
        <el-table-column prop="01_late" label="遲到" />
        <el-table-column prop="02_flag" label="升旗" />
        <el-table-column prop="03_lunch_break" label="午休" />
        <el-table-column prop="04_class_conduct" label="課堂常規" />
        <el-table-column prop="05_daily_conduct" label="行為常規" />
        <el-table-column prop="21_class_tidy" label="教室區整潔" />
        <el-table-column prop="22_outdoor_tidy" label="外掃區整潔" />
        <el-table-column prop="10_order_sub_total" label="秩序總分" />
        <el-table-column prop="20_tidy_sub_total" label="整潔總分" />
      </el-table>

      <!-- 改用 BasePagination 組件 -->
      <BasePagination v-if="records.data && records.data.length > 0" v-model:pagination="pagination"
        @size-change="handleSizeChange" @current-change="handleCurrentChange" />

      <!-- 修正空數據判斷 -->
      <div v-if="!loading && (!records.data || records.data.length === 0)" class="text-center py-8 text-gray-500">
        {{ hasFilter ? '無符合條件的資料' : '請選擇班級或周別以查看資料' }}
      </div>
    </div>
  </div>
</template>



<style scoped>
  .el-select {
    width: 200px;
  }

/* 添加連結樣式 */
.clickable-link {
  @apply text-blue-600 hover:text-blue-800 hover:underline cursor-pointer;
}

</style>
