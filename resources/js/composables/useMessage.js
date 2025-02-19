import { ElMessage } from "element-plus";

export function useMessage() {
    const success = (message) => ElMessage.success(message);
    const error = (message) => ElMessage.error(message);

    return { success, error };
}

