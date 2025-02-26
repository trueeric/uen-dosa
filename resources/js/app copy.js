import '../css/app.css';
import './bootstrap';
import 'element-plus/dist/index.css';

import ElementPlus from 'element-plus';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';


// 導入 UiButton 組件
import UiButton from '@/components/UiButton.vue';
import UiDatePicker from '@/components/UiDatePicker.vue';
import UiTable from '@/components/UiTable.vue';

import * as ElementPlusIconsVue from '@element-plus/icons-vue'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
        setup({ el, App, props, plugin }) {
          // 創建 Vue 應用實例
          const app = createApp({ render: () => h(App, props) });

          // 全局註冊 UiButton 組件
          app.component('UiButton', UiButton);

          // 全局註冊 UiDatePicker 組件
          app.component('UiDatePicker', UiDatePicker);

          // 在 Vue 應用中全局註冊組件
          app.component('UiTable', UiTable);

          // 全局註冊圖標（可選）
        for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
          app.component(key, component)
        }

          // 使用插件並掛載應用
          app.use(plugin)
             .use(ElementPlus)
             .use(ZiggyVue)
             .mount(el);

          return app;
      },
      progress: {
          color: '#4B5563',
      },
});
