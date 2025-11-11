import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
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
    ],
    // server: {
    //     host: '0.0.0.0',
    //     port: 5173,
    //     hmr: {
    //     host: '10.78.120.40',
    //     },
    // },
    // server: {
    //     host: '0.0.0.0',
    // },
    // build: {
    //     manifest: true,
    //     rollupOptions: {
    //         output: {
    //             assetFileNames: (assetInfo) => {
    //                 let extType = assetInfo.name.split('.').at(1);
    //                 if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
    //                     extType = 'img';
    //                 }
    //                 return `assets/${extType}/[name]-[hash][extname]`;
    //             },
    //         },
    //     },
    // },
});