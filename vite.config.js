import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({ input: ['resources/js/app.jsx'], refresh: true }),
        react(),
    ],
    resolve: { alias: { '@': '/resources/js' } },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    // React + Inertia — core runtime, jarang berubah
                    vendor: ['react', 'react-dom', '@inertiajs/react'],
                    // State management — pisah agar bisa cache terpisah
                    store: ['zustand'],
                    // Icons — besar, jarang berubah
                    icons: ['lucide-react'],
                },
            },
        },
        // Kurangi threshold warning chunk size
        chunkSizeWarningLimit: 800,
    },
    server: {
        host: '127.0.0.1',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
