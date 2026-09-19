import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { resolve } from 'node:path';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            refresh: true,
        }),
        react(),
    ],
    resolve: {
        alias: [
            {
                find: '@tabler/icons-react',
                replacement: '@tabler/icons-react/dist/cjs/tabler-icons-react.cjs',
            },
            {
                find: /^react-aria\/private\/(.*)$/,
                replacement: resolve('node_modules/react-aria/dist/private/$1.js'),
            },
            {
                find: /^react-aria\/(.*)$/,
                replacement: resolve('node_modules/react-aria/dist/exports/$1.js'),
            },
        ],
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        origin: 'http://127.0.0.1:5173',
        cors: {
            origin: ['http://127.0.0.1:8000', 'http://127.0.0.1:8080', 'http://localhost:8000', 'http://localhost:8080'],
            methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            credentials: true,
        },
        hmr: {
            host: '127.0.0.1',
            port: 5173,
            clientPort: 5173,
        },
        watch: {
            usePolling: true,
            interval: 1000,
        },
    },
});
