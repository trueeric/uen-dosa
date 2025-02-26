// /js/composables/useDateFormatter.js
import dayjs from "dayjs";
import utc from "dayjs/plugin/utc";
import timezone from "dayjs/plugin/timezone";

// 註冊插件
dayjs.extend(utc);
dayjs.extend(timezone);

// 設置默認時區為台北時間
dayjs.tz.setDefault("Asia/Taipei");

export const useDateFormatter = () => {
  const formatDate = (date) => {
    if (!date) return ["", "", "", ""];

    // 使用 tz() 確保時區正確
    const d = dayjs(date).tz();

    return [
      d.format("YYYY-MM-DD"),
      d.format("YYYY-MM-DD HH:mm:ss"),
      d.format("MMDD HHmm"),
      d.format("MMDD"),
    ];
  };

  return { formatDate };
};
