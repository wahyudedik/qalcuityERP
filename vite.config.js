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
        include: ['alpinejs', 'chart.js', 'dompurify', 'marked'],
        // Exclude large dependencies from pre-bundling to reduce memory
        exclude: []
    },

    // Server configuration
    server: {
        hmr: {
            host: 'localhost',
            protocol: 'ws',
            // Reduce HMR memory usage
            overlay: false
        },
        // Increase file watcher limit for large projects
        watch: {
            usePolling: false,
            interval: 100,
            // Ignore large directories to reduce memory usage
            ignored: [
                '**/node_modules/**',
                '**/storage/**',
                '**/vendor/**',
                '**/.git/**',
                '**/bootstrap/cache/**'
            ]
        },
        // Limit concurrent requests to reduce memory
        warmup: {
            clientFiles: [
                'resources/js/app.js',
                'resources/css/app.css'
            ]
        }
    },
    // Increase Node.js memory limit for Vite
    define: {
        'process.env.NODE_OPTIONS': '--max-old-space-size=4096'
    }
});
