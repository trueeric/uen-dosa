<script setup>
	import { ref, computed, onMounted, watch } from 'vue'
	import {
		ElForm,
		ElFormItem,
		ElInput,
		ElSelect,
		ElOption,
		ElButton,
		ElMessage,
		ElDatePicker,
		ElInputNumber,
		ElMessageBox,
	} from 'element-plus'
	import { useForm, usePage, router } from '@inertiajs/vue3'
	import { useDateFormatter } from '@/composables/useDateFormatter'

	const props = defineProps({
		semesterInfo: Object,
		scoreItems: Object,
		userInfo: Object,
		userTodayRecords: Array, // 新增這個 prop
	})

	//* 除錯 預設為 false，需要時可以改為 true
	// const debug = ref(false)
	const debug = ref(true)

	// 目前日期及時間
	const { formatDate } = useDateFormatter()
	const currentDate = ref(formatDate(new Date())[0])
	const currentOnlyDate = ref(formatDate(new Date())[4])
	const currentTime = ref(formatDate(new Date())[1])

	// 從 flash 消息中獲取最新記錄
	const page = usePage()
	const flashUserTodayRecords = computed(() => {
		// 安全地訪問 flash 屬性
		return page.props.flash?.userTodayRecords || []
	})

	// 合併 props 中的記錄和 flash 中的記錄
	const todayRecords = computed(() => {
		return flashUserTodayRecords.value.length > 0 ? flashUserTodayRecords.value : props.userTodayRecords || []
	})

	// 編輯模式相關
	const isEditMode = ref(false)
	const editingRecordId = ref(null)

	// 使用 Inertia 表單
	const form = useForm({
		// 用戶需要輸入的欄位
		target_no: '', // class or student_no
		score_no: '', // o-xxx or t-xxx
		scored_by: props.userInfo.identity_no, // 預設值，但用戶可以修改
		times: 1, // 預設值，可改
		score_date: currentDate.value, // 評分日期
		description: '', // 用戶可以輸入的備註

		// 自動設置的欄位（前端預設值，但後端會重新設置）
		target_type: '', // 根據 target_no 自動計算
		target_id: '', // 後端處理
		recorded_by: props.userInfo.user_id, // 當前用戶 ID
		semester: props.semesterInfo.semester, // 當前學期
		status: 'pending', // 預設狀態
	})

	// 螢幕寬度相關
	const screenWidth = ref(window.innerWidth)
	const isSmallScreen = computed(() => screenWidth.value < 768)
	const isMediumScreen = computed(() => screenWidth.value >= 768 && screenWidth.value < 1024)
	const isLargeScreen = computed(() => screenWidth.value >= 1024)

	// 監聽螢幕寬度變化
	onMounted(() => {
		window.addEventListener('resize', handleResize)
	})

	const handleResize = () => {
		screenWidth.value = window.innerWidth
	}

	// 用於存儲所有分數項目
	const allScoreItems = ref([])
	// 項目代碼是否有效
	const isValidScoreNo = ref(true)
	// 當前選擇的項目信息
	const currentScoreItem = ref(null)
	// 錯誤訊息
	const scoreNoError = ref('')

	// 初始化數據
	onMounted(() => {
		allScoreItems.value = props.scoreItems || []

		window.addEventListener('resize', handleResize)

		// 調試 page.props 結構
		if (debug.value) {
			console.log('Page props structure:', page.props)
		}

		// 檢查是否有錯誤訊息
		if (errors.value.duplicate) {
			ElMessage.warning(errors.value.duplicate)
		}
	})

	// 驗證項目代碼
	const validateScoreNo = () => {
		if (!form.score_no) {
			isValidScoreNo.value = false
			scoreNoError.value = '請輸入項目代碼'
			currentScoreItem.value = null
			return false
		}

		const foundItem = allScoreItems.value.find((item) => item.score_no === form.score_no)

		if (foundItem) {
			isValidScoreNo.value = true
			scoreNoError.value = ''
			currentScoreItem.value = foundItem
			return true
		} else {
			isValidScoreNo.value = false
			scoreNoError.value = '無效的項目代碼'
			currentScoreItem.value = null
			return false
		}
	}

	// 監聽頁面錯誤訊息
	const errors = computed(() => page.props.errors || {})

	// 監聽 score_no 變化
	watch(
		() => form.score_no,
		(newValue) => {
			if (newValue) {
				validateScoreNo()
			} else {
				isValidScoreNo.value = true
				scoreNoError.value = ''
				currentScoreItem.value = null
			}
		},
	)

	// 更新 target_type 的方法 <4 'class', >4 'student'
	// 根據 target_no 的長度來判斷是班級還是學生
	const updateTargetType = () => {
		form.target_type = form.target_no.length > 4 ? 'student' : 'class'
	}

	// 監聽 target_no 的變化
	watch(
		() => form.target_no,
		(newValue) => {
			updateTargetType()
		},
	)

	// 檢查是否有重複記錄
	const checkDuplicateRecord = () => {
		if (!form.target_no || !form.score_no) return false

		// 檢查今日記錄中是否有相同的 target_no 和 score_no 組合
		return todayRecords.value.some(
			(record) =>
				record.target_no &&
				record.score_no &&
				record.target_no.toUpperCase() === form.target_no.toUpperCase() &&
				record.score_no === form.score_no,
		)
	}

	// 處理編輯記錄
	const handleEdit = (row) => {
		// 將記錄數據填充到表單中
		form.target_no = row.target_no || row.target_no_caseif
		form.score_no = row.score_no
		form.scored_by = row.scored_by || props.userInfo.identity_no
		form.times = row.times || 1
		form.score_date = row.score_date
		form.description = row.description || ''

		// 設置編輯模式
		isEditMode.value = true
		editingRecordId.value = row.id

		// 更新 target_type
		updateTargetType()

		// 驗證 score_no
		validateScoreNo()

		// 滾動到表單頂部
		window.scrollTo({ top: 0, behavior: 'smooth' })

		// 顯示提示訊息
		ElMessage.info('請修改記錄後重新提交')
	}

	// 取消編輯
	const cancelEdit = () => {
		isEditMode.value = false
		editingRecordId.value = null
		reset()
	}

	// 處理刪除記錄
	const handleDelete = (row) => {
		ElMessageBox.confirm('確定要刪除此記錄嗎？此操作可以在管理界面恢復', '警告', {
			confirmButtonText: '確定',
			cancelButtonText: '取消',
			type: 'warning',
		})
			.then(() => {
				// 發送刪除請求
				router.delete(route('uen-score-records.destroy', { uen_score_record: row.id }), {
					onSuccess: (page) => {
						ElMessage.success('記錄已成功刪除')

						// 從 flash 消息中獲取更新後的記錄
						if (page.props.flash?.userTodayRecords) {
							// 直接使用後端返回的最新記錄
							todayRecords.value = page.props.flash.userTodayRecords
						} else {
							// 或者從本地數據中移除該記錄
							const index = todayRecords.value.findIndex((record) => record.id === row.id)
							if (index !== -1) {
								todayRecords.value.splice(index, 1)
							}
						}
					},
					onError: (errors) => {
						ElMessage.error('刪除失敗：' + (errors.message || '未知錯誤'))
					},
				})
			})
			.catch(() => {
				ElMessage.info('已取消刪除')
			})
	}

	// 提交表單
	const submitForm = () => {
		// 驗證項目代碼
		if (!validateScoreNo()) {
			ElMessage.error(scoreNoError.value)
			return
		}
		if (isEditMode.value) {
			// 更新模式
			form.put(route('uen-score-records.update', { uen_score_record: editingRecordId.value }), {
				onSuccess: () => {
					ElMessage.success('成績記錄已成功更新')
					isEditMode.value = false
					editingRecordId.value = null
					form.reset()
					// 重置後恢復預設值
					form.scored_by = props.userInfo.identity_no
					form.times = 1
					form.score_date = currentDate.value
					isValidScoreNo.value = true
					scoreNoError.value = ''
					currentScoreItem.value = null
				},
				onError: (errors) => {
					ElMessage.error('更新表單時發生錯誤')
				},
			})
		} else {
			// 創建模式

			// 檢查是否已存在相同記錄
			const isDuplicate = checkDuplicateRecord()
			if (isDuplicate) {
				ElMessage.warning(
					`今日已有相同班級/學號 "${form.target_no}" 和項目代碼 "${form.score_no}" 的記錄，請勿重複輸入`,
				)
				return
			}

			form.post(route('uen-score-records.store'), {
				onSuccess: () => {
					ElMessage.success('成績記錄已成功創建')
					form.reset()
					// 重置後恢復預設值
					form.scored_by = props.userInfo.identity_no
					form.times = 1
					form.score_date = currentDate.value
					isValidScoreNo.value = true
					scoreNoError.value = ''
					currentScoreItem.value = null
				},
				onError: (errors) => {
					ElMessage.error('提交表單時發生錯誤')
				},
			})
		}
	}

	// 重置表單
	const reset = () => {
		form.reset()
		// 重置後恢復預設值
		form.scored_by = props.userInfo.identity_no
		form.times = 1
		form.score_date = currentDate.value
		isValidScoreNo.value = true
		scoreNoError.value = ''
		currentScoreItem.value = null
	}
</script>

<template>
	<div class="container mx-auto p-4">
		<!-- 如果需要偵錯資訊，可以加入這段 -->
		<div v-if="debug" class="bg-gray-100 p-4 mb-4 rounded">
			<h3 class="font-bold mb-2">Debug Information:</h3>
			<!-- <pre>{{ semesterInfo }}</pre> -->
			<!-- <pre>{{ userInfo }}</pre> -->
		</div>
	</div>
	<div class="score-record-create container">
		<div class="flex flex-wrap justify-between items-center mb-4">
			<h1 class="text-2xl font-bold mb-2 md:mb-0">
				{{ semesterInfo.semester }}榮譽競賽{{ isEditMode ? '編輯' : '登錄' }}
			</h1>
			<div class="flex flex-wrap items-center gap-4">
				<div class="text-gray-600">登錄人:{{ userInfo.user_name }}</div>
				<div class="text-gray-600">編號:{{ userInfo.identity_no }}</div>
			</div>
		</div>

		<el-form :model="form" :label-width="isSmallScreen ? '80px' : '120px'" class="score-form">
			<!-- 自適應布局 -->
			<div :class="{ 'grid grid-cols-1 md:grid-cols-2 gap-4': !isSmallScreen }">
				<!-- 第一列 -->
				<el-form-item label="輸入號">
					<el-input v-model="form.target_no" type="string" placeholder="請輸入班別或學號" />
				</el-form-item>

				<!-- 第二列：改為直接輸入項目代碼 -->
				<el-form-item label="項目代碼" :class="{ 'is-error': !isValidScoreNo }" :error="scoreNoError">
					<el-input
						v-model="form.score_no"
						type="string"
						placeholder="請輸入項目代碼 (例如: o-123, t-456)"
						@blur="validateScoreNo"
					/>
					<!-- 顯示找到的項目信息 -->
					<div v-if="currentScoreItem" class="mt-1 text-sm text-green-600">
						找到項目: {{ currentScoreItem.item_name }} ({{ currentScoreItem.points }}分)
					</div>
				</el-form-item>

				<!-- 第三列 -->
				<el-form-item label="評分人">
					<el-input v-model="form.scored_by" type="string" placeholder="評分人學號/員工編號" />
				</el-form-item>

				<!-- 新增：次數 -->
				<el-form-item label="次數">
					<el-input-number v-model="form.times" :min="1" :max="100" placeholder="計算次數" style="width: 100%" />
				</el-form-item>

				<!-- 新增：評分日期 -->
				<el-form-item label="評分日期">
					<el-date-picker v-model="form.score_date" type="date" placeholder="選擇日期" style="width: 100%" />
				</el-form-item>

				<!-- 備註 (跨越整行) -->
				<el-form-item label="備註" :class="{ 'md:col-span-2': !isSmallScreen }">
					<el-input v-model="form.description" type="textarea" :rows="2" placeholder="備註" />
				</el-form-item>
			</div>

			<!-- 按鈕區域 -->
			<el-form-item class="mt-4">
				<div class="flex flex-wrap gap-2">
					<template v-if="isEditMode">
						<el-button type="primary" @click="submitForm">更新</el-button>
						<el-button @click="cancelEdit">取消</el-button>
					</template>
					<template v-else>
						<el-button type="primary" @click="submitForm">提交</el-button>
						<el-button @click="reset">重置</el-button>
					</template>
				</div>
			</el-form-item>
		</el-form>
		<!-- 添加今日記錄表格 -->
		<div class="mt-8">
			<h2 class="text-xl font-bold mb-4">今( {{ currentOnlyDate }} )日輸入記錄</h2>
			<!-- <pre>{{ todayRecords[0] }}</pre> -->
			<el-table :data="todayRecords" style="width: 100%" border v-if="todayRecords.length > 0">
				<el-table-column prop="id" label="ID" width="80" />
				<el-table-column label="班級/姓名" width="100">
					<template #default="{ row }">
						<div>
							<div>
								{{ row.target_no_caseif }}
							</div>
							<div>
								{{ row.target_name }}
							</div>
						</div>
					</template>
				</el-table-column>
				<!-- 評分項目 -->
				<el-table-column label="評分項目" width="120">
					<template #default="{ row }">
						<div>
							<div>{{ row.score_no }}</div>
							<div>{{ row.score_item_name }}</div>
						</div>
					</template>
				</el-table-column>
				<el-table-column label="評分小計" width="100">
					<template #default="{ row }">
						<div :class="row.subtotal >= 0 ? 'text-indigo-500' : 'text-red-500'">{{ row.subtotal }}</div>
						<div>( {{ row.points }} x {{ row.times }} )</div>
					</template>
				</el-table-column>
				<!-- 評分日期 -->
				<el-table-column label="登錄日期" width="100" align="center">
					<template #default="{ row }">
						{{ formatDate(row.score_date)[3] }}
					</template>
				</el-table-column>
				<el-table-column prop="status" label="狀態" width="100">
					<template #default="{ row }">
						<el-tag :type="row.status === 'pending' ? 'warning' : 'success'">
							{{ row.status === 'pending' ? '待審核' : '已審核' }}
						</el-tag>
					</template>
				</el-table-column>
				<el-table-column label="操作" width="150" align="center">
					<template #default="{ row }">
						<div class="flex gap-1 justify-center">
							<el-button type="primary" size="small" @click="handleEdit(row)">編輯</el-button>
							<el-button type="danger" size="small" @click="handleDelete(row)">刪除</el-button>
						</div>
					</template>
				</el-table-column>
			</el-table>

			<div v-else class="text-center py-8 text-gray-500">今日尚無輸入記錄</div>
		</div>
	</div>
</template>

<style>
	.score-record-create {
		max-width: 1000px;
		margin: 0 auto;
		padding: 20px;
	}

	.semester-info {
		margin-bottom: 20px;
		padding: 10px;
		border: 1px solid #eee;
		border-radius: 4px;
	}

	.score-form {
		margin-top: 20px;
	}

	/* 錯誤樣式 */
	.is-error .el-input__wrapper {
		box-shadow: 0 0 0 1px #f56c6c inset !important;
	}

	/* 響應式樣式 */
	@media (max-width: 768px) {
		.score-record-create {
			padding: 10px;
		}
	}
</style>
