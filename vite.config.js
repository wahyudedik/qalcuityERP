import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/offline-manager.js',
                'resources/js/offline-pos.js',
                'resources/js/offline-status.js',
            ],
            refresh: true,
        }),
        viteStaticCopy({
            targets: [
                {
                    src: 'resources/js/sw.js',
                    dest: '../public',
                    rename: 'sw.js'
                }
            ]
        })
    ],
    build: {
        // Code splitting configuration for Vite 8 / Rolldown
        rollupOptions: {
            output: {
                // Asset naming with content hash for caching
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]'
            }
        },

        // Minification settings
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: false, // Keep console logs in production
                drop_debugger: true,
                pure_funcs: ['console.info', 'console.debug'], // Only remove these
                passes: 2 // Multiple passes for better optimization
            },
            format: {
                comments: false // Remove license comments
            }
        },

        // Performance optimizations
        chunkSizeWarningLimit: 500, // Warn if chunk > 500kb
        reportCompressedSize: true,
        cssCodeSplit: true, // Split CSS by JS imports
        sourcemap: false // Disable source maps in production
    },

    // Development optimizations
    optimizeDeps: {
        include: ['alpinejs', 'chart.js', 'dompurify', 'marked']
    },

    // Server configuration
    server: {
        hmr: {
            host: 'localhost',
            protocol: 'ws'
        }
    }
});
