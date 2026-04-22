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
                    // React + Inertia dipisah agar browser bisa cache vendor terpisah
                    vendor: ['react', 'react-dom', '@inertiajs/react'],
                    // Filament/admin deps (lucide, dll) dipisah dari customer pages
                    icons: ['lucide-react'],
                },
            },
        },
        // Kurangi threshold warning chunk size
        chunkSizeWarningLimit: 800,
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
