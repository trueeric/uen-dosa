import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js', // 主入口文件
                'resources/js/ssr.js', // SSR 文件
            ],
            refresh: true, // 啟用熱更新
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'), // 設置 @ 別名
        },
    },
    build: {
        outDir: 'public/build', // 指定輸出目錄
        sourcemap: false, // 禁用 source map，提升打包速度
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['vue'], // 將 Vue 分離為單獨的 chunk
                },
            },
        },
    },
    server: {
        host: '0.0.0.0', // 支持局域網訪問
        port: 3000, // 自定義開發伺服器埠
        hmr: {
            host: 'localhost', // 熱更新的主機名
        },
    },
});
