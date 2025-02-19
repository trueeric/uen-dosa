<template>
  <el-date-picker
    :model-value="value"
    @update:model-value="onUpdate"
    :type="type"
    :placeholder="placeholder"
    :format="format"
  />
</template>

<script>
export default {
  name: "UiDatePicker",
  props: {
    modelValue: {
      type: [String, Date],
      default: "",
    },
    type: {
      type: String,
      default: "date", // 預設類型為日期選擇
    },
    placeholder: {
      type: String,
      default: "選擇日期",
    },
    format: {
      type: String,
      default: "YYYY-MM-DD",
    },
  },
  emits: ["update:modelValue"],
  data() {
    return {
      value: this.modelValue, // 本地數據綁定
    };
  },
  watch: {
    modelValue(newVal) {
      this.value = newVal; // 當外部值變化時，同步本地值
    },
  },
  methods: {
    onUpdate(newValue) {
      this.$emit("update:modelValue", newValue); // 將內部值更新傳遞給父組件
    },
  },
};
</script>

<style scoped>
/* 可選：自定義樣式 */
</style>
