// resources/js/app.js

// 樣式導入
import '../css/app.css';
import './bootstrap';
import 'element-plus/dist/index.css';

// 核心套件導入
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Element Plus 相關導入
import ElementPlus from 'element-plus';
import zhTw from 'element-plus/dist/locale/zh-tw.mjs';
import * as ElementPlusIconsVue from '@element-plus/icons-vue';

// 自定義組件導入
import {
  UiButton,
  UiDatePicker,
  UiTable
} from '@/components';

// 環境變量檢查
const requiredEnvVars = ['VITE_APP_NAME', 'VITE_APP_URL'];
requiredEnvVars.forEach(varName => {
    if (!import.meta.env[varName]) {
        console.warn(`警告: 環境變量 ${varName} 未設置`);
    }
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // 配置 Element Plus
        app.use(ElementPlus, {
            locale: zhTw,
            size: 'default',
            zIndex: 3000
        });

        // 使用核心插件
        app.use(plugin)
           .use(ZiggyVue);

        // 註冊全局 UI 組件
        const globalComponents = {
          UiButton,
          UiDatePicker,
          UiTable
        };

        Object.entries(globalComponents).forEach(([name, component]) => {
          app.component(name, component);
        });

        // 註冊 Element Plus 圖標
        for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
          app.component(key, component);
        }

        // 全局錯誤處理
        app.config.errorHandler = (err, vm, info) => {
            console.error('Vue 錯誤:', err);
            console.error('錯誤信息:', info);
            // 可以在這裡添加錯誤上報邏輯
        };

        // Element Plus 全局配置
        app.config.globalProperties.$ELEMENT = {
            size: 'default',
            zIndex: 3000,
        };

        // 開發環境配置
        if (import.meta.env.DEV) {
            app.config.performance = true;
            app.config.devtools = true;
        }

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
