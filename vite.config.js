
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import path from 'path'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'

export default defineConfig({
    plugins: [
        AutoImport({
            imports: ['vue'],
            dts: 'resources/js/types/auto-imports.d.ts',
        }),

        Components({
            dirs: ['resources/js/components/lib'],
            extensions: ['vue'],
            dts: 'resources/js/types/components.d.ts',
        }),

        laravel({
            input: [
                'resources/js/entrypoint/app.js',
                'resources/js/entrypoint/login.js',
            ],
            refresh: true,
        }),

        vue({
            template: {
                compilerOptions: {
                    isCustomElement: (tag) => tag === 'altcha-widget',
                },
            },
        }),

        tailwindcss(),
    ],

    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
})
