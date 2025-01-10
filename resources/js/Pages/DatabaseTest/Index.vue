<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">資料庫連接狀態</h1>

    <!-- 錯誤訊息顯示 -->
    <div
      v-if="error"
      class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded"
    >
      {{ error }}
    </div>

    <div v-if="Object.keys(dbStatus).length > 0" class="grid gap-4">
      <div
        v-for="(info, connection) in dbStatus"
        :key="connection"
        class="p-4 border rounded-lg"
      >
        <h2 class="font-semibold">{{ connection }}</h2>
        <div class="mt-2">
          <div class="flex items-center">
            <span class="mr-2">狀態:</span>
            <span
              :class="{
                'text-green-600': info.status === 'Connected',
                'text-red-600': info.status === 'Failed',
              }"
            >
              {{ info.status }}
            </span>
          </div>
          <div v-if="info.database">資料庫: {{ info.database }}</div>
          <div v-if="info.error" class="text-red-600 text-sm mt-1">
            錯誤: {{ info.error }}
          </div>
        </div>
      </div>
    </div>

    <button
      @click="checkConnections"
      :disabled="isLoading"
      class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:bg-blue-300"
    >
      {{ isLoading ? "檢查中..." : "重新檢查連接" }}
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const dbStatus = ref({});
const error = ref(null);
const isLoading = ref(false);

const checkConnections = async () => {
  error.value = null;
  isLoading.value = true;

  try {
    const response = await axios.get("/api/database-status");
    dbStatus.value = response.data;
  } catch (err) {
    console.error("檢查資料庫連接時發生錯誤:", err);
    error.value =
      err.response?.data?.message ||
      "檢查資料庫連接時發生錯誤，請檢查伺服器日誌";
    dbStatus.value = {};
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  checkConnections();
});
</script>
