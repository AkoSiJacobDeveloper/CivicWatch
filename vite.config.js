import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    // server: {
    //     host: '0.0.0.0',
    //     port: 5173,
    //     strictPort: true,
    //     hmr: {
    //         host: '192.168.100.4', 
    //     },
    //     cors: {
    //         origin: ['http://192.168.100.4:8000', 'http://localhost:8000'],
    //         credentials: true,
    //     },
    // },
    // 
    // server: {
    //     host: '192.168.100.4', // Your WiFi IP
    //     port: 5173,
    //     strictPort: true,
    //     hmr: {
    //         host: '192.168.100.4',
    //         protocol: 'ws',
    //     },
    //     cors: true, // Simple CORS enable
    // },
    // server: {
    //     host: '0.0.0.0',
    //     port: 5173,
    //     hmr: {
    //         host: '10.208.188.40', // Your actual IP
    //         port: 5173,
    //         protocol: 'ws'
    //     },
    //     watch: {
    //         usePolling: true,
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
    define: {
    __VUE_OPTIONS_API__: true,
    __VUE_PROD_DEVTOOLS__: false,
    }
});
