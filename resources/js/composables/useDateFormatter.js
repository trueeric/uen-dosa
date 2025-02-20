// /js/composables/useDateFormatter.js
import dayjs from "dayjs";

/**
 * 格式化日期時間為多種格式
 * @param {string|Date} date - 要格式化的日期
 * @returns {Array} 包含多種格式日期的陣列
 */
export const useDateFormatter = () => {
  const formatDate = (date) => {
    if (!date) return ["", "", ""]; // 如果 date 無效，返回空陣列

    return [
      dayjs(date).format("YYYY-MM-DD"), // 格式 1: 2025-02-19
      dayjs(date).format("YYYY-MM-DD HH:mm:ss"), // 格式 2: 2025-02-19 17:04:51
      dayjs(date).format("MMDD HHmm"), // 格式 3: 0219 1704
      dayjs(date).format("MMDD"), // 格式 4: 0219
    ];
  };

  return { formatDate };
};
