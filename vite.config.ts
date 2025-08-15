import vue from '@vitejs/plugin-vue';
import autoprefixer from 'autoprefixer';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { resolve } from 'node:path';
import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

// ---- Environment Detection ----
const isDdev = !!process.env.DDEV_PRIMARY_URL;
const isProduction = process.env.NODE_ENV === 'production';

// ---- Defaults ----
const PORT = 5173;
const DDEV_URL = process.env.DDEV_PRIMARY_URL;
const DEV_ORIGIN = isDdev ? `${DDEV_URL}:${PORT}` : undefined;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss()
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            //'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
            'img': path.resolve(__dirname, './resources/img'),
        },
    },
    css: {
        postcss: {
            plugins: [autoprefixer],
        },
    },
    server: !isProduction && isDdev ? {
        host: '0.0.0.0',
        port: PORT,
        strictPort: true,
        origin: DEV_ORIGIN,
        cors: {
            origin: /https?:\/\/([A-Za-z0-9-\.]+)?(\.ddev\.site)(?::\d+)?$/,
        },
    } : {},
});
