import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    // server: {
    //     host: '0.0.0.0', // allow all devices in network to connect
    //     port: 5173,
    //     strictPort: true,
    //     origin: 'http://10.0.9.119:5173', // this is Vite's own URL
    //     cors: {
    //         origin: 'http://10.0.9.119:8000', // ✅ allow Laravel server
    //         credentials: true,
    //     },
    // },
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
});
