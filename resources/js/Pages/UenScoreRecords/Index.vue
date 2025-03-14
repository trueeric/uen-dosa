<script setup>
	import { ref, reactive, onMounted, watch, computed } from 'vue'
	import { usePage, router } from '@inertiajs/vue3'
	import { ElMessage, ElMessageBox } from 'element-plus'
	import { Refresh, Plus, Search, Edit, Delete, CircleClose } from '@element-plus/icons-vue'
	import { useDateFormatter } from '@/composables/useDateFormatter'
	import BasePagination from '@/components/BasePagination.vue'

	// 定義 props
	const props = defineProps({
		records: {
			type: Object,
			required: true,
			default: () => ({
				data: [],
				meta: {
					current_page: 1,
					per_page: 10,
					total: 0,
				},
			}),
		},
		weekOptions: {
			type: Array,
			default: () => [],
		},
		filters: {
			type: Object,
			default: () => ({}),
		},
	})

	//* 除錯 預設為 false，需要時可以改為 true
	const debug = ref(false)
	// const debug = ref(true);

	// 添加重置標記
	const isResetting = ref(false)

	// 獲取頁面屬性
	const page = usePage()

	// 如果需要響應式的訪問 debugSql
	const debugSql = computed(() => page.props.debugSql)

	// 數據響應式處理
	const records = ref(props.records)
	const weekOptions = ref(props.weekOptions)

	// 分頁設置
	const pagination = reactive({
		current_page: Number(props.records?.meta?.current_page) || 1,
		per_page: Number(props.records?.meta?.per_page) || 10,
		total: Number(props.records?.meta?.total) || 0,
	})

	// 添加檢查是否有篩選條件的計算屬性
	const hasFilter = computed(() => {
		return (
			!!searchForm.class_no || !!searchForm.week_no || (searchForm.date_range && searchForm.date_range.length === 2)
		)
	})

	// 日期格式化
	const { formatDate } = useDateFormatter()

	// 搜尋表單 - 使用 props.filters 進行初始化
	const searchForm = reactive({
		class_no: props.filters?.class_no || '',
		week_no: props.filters?.week_no || '',
		date_range: props.filters?.date_range || [],
	})

	// 加載狀態
	const loading = ref(false)

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

	// 搜尋資料
	const handleSearch = () => {
		// 如果正在重置，直接返回
		if (isResetting.value) {
			return
		}

		// 檢查是否有搜尋條件
		if (!hasFilter.value) {
			ElMessage.warning('請選擇搜尋條件後再搜尋')
			return
		}

		loading.value = true

		const [startDate, endDate] = searchForm.date_range || [null, null]

		// 建立查詢參數物件
		const params = {
			page: pagination.current_page,
			per_page: pagination.per_page,
		}

		// 條件性添加其他參數
		if (searchForm.class_no?.trim()) {
			params.class_no = searchForm.class_no.trim()
		}

		if (searchForm.week_no) {
			params.week_no = searchForm.week_no
		}

		if (startDate) {
			params.start_date = formatDate(startDate)[0]
		}

		if (endDate) {
			params.end_date = formatDate(endDate)[0]
		}

		router.get(route('uen-score-records.index'), params, {
			preserveState: true,
			preserveScroll: true,
			onBefore: () => {
				loading.value = true
			},
			onSuccess: (page) => {
				if (page.props.records) {
					records.value = page.props.records
					// 更新分頁信息
					if (page.props.records.meta) {
						const meta = page.props.records.meta
						pagination.current_page = Number(meta.current_page)
						pagination.per_page = Number(meta.per_page)
						pagination.total = Number(meta.total)
					}
					ElMessage.success('搜尋成功')
				} else {
					console.error('No records found in response')
					ElMessage.warning('沒有找到相關記錄')
					// 重置數據和分頁
					resetDataAndPagination()
				}
			},
			onError: (errors) => {
				console.error('Search error:', errors)
				ElMessage.error('搜尋失敗：' + Object.values(errors).join(', '))
				resetDataAndPagination()
			},
			onFinish: () => {
				loading.value = false
			},
		})
	}

	// 新增一個重置數據和分頁的輔助函數
	const resetDataAndPagination = () => {
		records.value = {
			data: [],
			meta: {
				current_page: 1,
				per_page: 10,
				total: 0,
			},
		}
		pagination.current_page = 1
		pagination.per_page = 10
		pagination.total = 0
	}

	// 重置搜尋
	const resetSearch = () => {
		try {
			// 重置表單值
			searchForm.class_no = ''
			searchForm.week_no = ''
			searchForm.date_range = []

			// 重置分頁
			pagination.current_page = 1
			pagination.per_page = 10

			// 使用 handleSearch 來執行搜尋
			handleSearch()
		} catch (error) {
			console.error('Reset search error:', error)
			ElMessage.error('重置搜尋時發生錯誤')
		}
	}

	// 新增記錄
	const handleCreate = () => {
		router.visit(route('uen-score-records.create'))
	}

	// 編輯記錄
	const handleEdit = (row) => {
		router.visit(route('uen-score-records.edit', { id: row.id }))
	}

	// 刪除記錄
	const handleDelete = (row) => {
		ElMessageBox.confirm('確定要刪除此記錄嗎？此操作不可恢復', '警告', {
			confirmButtonText: '確定',
			cancelButtonText: '取消',
			type: 'warning',
		})
			.then(() => {
				router.delete(route('uen-score-records.destroy', { id: row.id }), {
					onSuccess: () => {
						ElMessage.success('刪除成功')
						handleSearch()
					},
					onError: (error) => {
						ElMessage.error('刪除失敗：' + error)
					},
				})
			})
			.catch(() => {
				ElMessage.info('已取消刪除')
			})
	}

	// 表格排序處理
	const handleSortChange = ({ prop, order }) => {
		if (!prop || !order) return

		const sortOrder = order === 'ascending' ? 'asc' : 'desc'
		router.get(
			route('uen-score-records.index'),
			{
				...route().params,
				sort_by: prop,
				sort_order: sortOrder,
			},
			{
				preserveState: true,
				preserveScroll: true,
			},
		)
	}

	// 修改 onMounted
	onMounted(() => {
		// 檢查是否有搜尋條件或 URL 參數
		const params = new URLSearchParams(window.location.search)
		const hasParams =
			params.has('class_no') || params.has('week_no') || params.has('start_date') || params.has('end_date')

		// 如果沒有搜尋條件和 URL 參數，直接顯示空白狀態
		if (!hasFilter.value && !hasParams) {
			records.value = {
				data: [],
				meta: {
					current_page: 1,
					per_page: 10,
					total: 0,
				},
			}
			return
		}

		// 如果有 URL 參數，更新搜尋表單
		if (hasParams) {
			if (params.has('class_no')) {
				searchForm.class_no = params.get('class_no')
			}
			if (params.has('week_no')) {
				searchForm.week_no = params.get('week_no')
			}
			const start_date = params.get('start_date')
			const end_date = params.get('end_date')
			if (start_date && end_date) {
				searchForm.date_range = [new Date(start_date), new Date(end_date)]
			}
			handleSearch()
		}
	})

	// 監聽 props 變化
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
		{ immediate: true, deep: true },
	)

	// 處理分頁更新
	const handlePaginationUpdate = (newPagination) => {
		pagination.current_page = newPagination.current_page
		pagination.per_page = newPagination.per_page
		handleSearch()
	}

	// 導出需要在模板中使用的變量和方法
	defineExpose({
		debug,
		loading,
		records,
		pagination,
		weekOptions,
		searchForm,
		handleSearch,
		resetSearch,
		handleCreate,
		handleEdit,
		handleDelete,
		handleSortChange,
		handleSizeChange,
		handleCurrentChange,
	})
</script>

<template>
	<div class="container mx-auto p-4">
		<!-- 如果需要偵錯資訊，可以加入這段 -->
		<div v-if="debug" class="bg-gray-100 p-4 mb-4 rounded">
			<h3 class="font-bold mb-2">Debug Information:</h3>
			<pre>{{ $page.props.debugSql }}</pre>

			<pre>分頁數據: {{ pagination }}</pre>
			<pre>Records Meta: {{ records?.meta }}</pre>
		</div>
	</div>
	<!-- <pre>{{ weekOptions }}</pre> -->
	<!-- <pre>{{ records.data[0] }}</pre> -->

	<div class="container mx-auto p-4">
		<div class="mb-4 flex justify-between items-center">
			<h1 class="text-2xl font-bold">成績記錄管理</h1>
			<div class="flex gap-2">
				<el-button @click="handleSearch">
					<el-icon>
						<Refresh />
					</el-icon>
					<span>重新整理</span>
				</el-button>
				<el-button variant="primary" @click="handleCreate">
					<el-icon>
						<Plus />
					</el-icon>
					新增記錄
				</el-button>
			</div>
		</div>

		<!-- 搜尋區塊 -->
		<div class="bg-white p-4 rounded-lg shadow mb-4">
			<div class="grid gap-4 grid-cols-1 sm:grid-cols-2 portrait:grid-cols-2">
				<!-- 搜尋班級 -->
				<el-input
					v-model="searchForm.class_no"
					placeholder="搜尋班級"
					clearable
					@keyup.enter="handleSearch"
					@clear="handleSearch"
				>
					<template #prefix>
						<el-icon>
							<Search />
						</el-icon>
					</template>
				</el-input>

				<!-- 選擇周別 -->
				<el-select
					v-model="searchForm.week_no"
					placeholder="選擇周別"
					clearable
					@change="handleSearch"
					@clear="handleSearch"
					class="w-full"
				>
					<template #prefix>
						<el-icon>
							<Search />
						</el-icon>
					</template>
					<el-option
						v-for="option in weekOptions"
						:key="option.value"
						:label="option.label"
						:value="option.value"
						placeholder="選擇周別"
						clearable
						@clear="handleSearch"
					/>
				</el-select>

				<!-- 日期範圍選擇 -->
				<el-date-picker
					v-model="searchForm.date_range"
					type="daterange"
					start-placeholder="選擇日期範圍"
					@clear="handleSearch"
					class="w-full"
				/>

				<!-- 搜尋按鈕區域 -->
				<div class="flex gap-2 justify-center">
					<el-button type="primary" round @click="handleSearch">
						<el-icon>
							<Search />
						</el-icon>
						搜尋
					</el-button>
					<el-button type="warning" round @click="resetSearch">
						<el-icon>
							<CircleClose />
						</el-icon>
						重置
					</el-button>
				</div>
			</div>
		</div>

		<!-- 資料表格 -->
		<div class="bg-white rounded-lg shadow overflow-hidden">
			<el-table :data="records.data" v-loading="loading" style="width: 100%" border>
				<!-- 序號 -->
				<el-table-column prop="id" label="序號" width="70" />

				<!-- 學期 -->
				<el-table-column prop="semester" label="學期" width="70" />

				<!-- 周別 -->
				<el-table-column prop="week_no" label="周別" width="80" header-align="center" align="right">
					<template #default="{ row }">
						<span class="pr-4">{{ row.week_no }}</span>
					</template>
				</el-table-column>

				<!-- 評分日期 -->
				<el-table-column label="日期" width="80" align="center">
					<template #default="{ row }">
						{{ formatDate(row.score_date)[3] }}
					</template>
				</el-table-column>
				<!-- 學期 -->

				<!-- 班級/姓名 -->
				<el-table-column prop="target_no" label="班級/姓名" width="90">
					<template #default="{ row }">
						<div>
							<div>{{ row.target_no_caseif }}</div>
							<div>{{ row.target_name }}</div>
						</div>
					</template>
				</el-table-column>

				<!-- 評分項目 -->
				<el-table-column label="評分項目">
					<template #default="{ row }">
						<div>
							<div>{{ row.score_item_name }}</div>
							<div :class="row.points >= 0 ? 'text-indigo-500' : 'text-red-500'">
								{{ row.points }}
							</div>
						</div>
					</template>
				</el-table-column>

				<!-- 次數 -->
				<el-table-column prop="times" label="次數" width="60" align="center" />

				<!-- 小計 -->
				<el-table-column prop="subtotal" label="小計" width="80" header-align="center" align="right">
					<template #default="{ row }">
						<span class="pr-4">{{ row.subtotal }}</span>
					</template>
				</el-table-column>

				<!-- 操作 -->
				<el-table-column label="操作" width="150" align="center">
					<template #default="{ row }">
						<div class="flex gap-1 justify-center">
							<el-button type="warning" size="small" @click="handleEdit(row)">
								<el-icon>
									<Edit />
								</el-icon>
								編輯
							</el-button>
							<el-button type="danger" size="small" @click="handleDelete(row)">
								<el-icon>
									<Delete />
								</el-icon>
								刪除
							</el-button>
						</div>
					</template>
				</el-table-column>
			</el-table>

			<!-- 添加空數據提示 -->
			<div v-if="!loading && (!records.data || records.data.length === 0)" class="text-center py-8 text-gray-500">
				{{ hasFilter ? '無符合條件的資料' : '請選擇搜尋條件以查看資料' }}
			</div>

			<!-- 使用分頁組件 -->
			<BasePagination
				v-if="records.data && records.data.length > 0"
				:total="records.meta.total"
				v-model:pagination="pagination"
				@size-change="handleSizeChange"
				@current-change="handleCurrentChange"
			/>
		</div>
	</div>
</template>
