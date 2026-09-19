import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            refresh: true,
        }),
        react(),
    ],
    server: {
        origin: 'http://127.0.0.1:5173',
        cors: {
            origin: 'http://127.0.0.1:8080',
        },
        hmr: {
            host: '127.0.0.1',
        },
        watch: {
            usePolling: true,
            interval: 1000,
        },
    },
});
