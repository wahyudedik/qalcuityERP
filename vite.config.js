import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig(({ mode }) => {
    // Load semua env variables (termasuk yang tidak diawali VITE_)
    const env = loadEnv(mode, process.cwd(), '');

    const appUrl = env.APP_URL || 'http://localhost';
    const appEnv = env.APP_ENV || 'production';
    const isLocal = appEnv === 'local';
    const isDebug = env.APP_DEBUG === 'true';

    // Parse host dari APP_URL (e.g. "http://qalcuityerp.test" → "qalcuityerp.test")
    const appHost = (() => {
        try { return new URL(appUrl).hostname; }
        catch { return 'localhost'; }
    })();

    return {
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                ],
                refresh: true,
            }),
            viteStaticCopy({
                targets: [
                    {
                        src: 'resources/js/sw.js',
                        dest: '../public',
                        rename: 'sw.js',
                    },
                ],
            }),
        ],

        build: {
            rollupOptions: {
                output: {
                    entryFileNames: 'assets/[name]-[hash].js',
                    chunkFileNames: 'assets/[name]-[hash].js',
                    assetFileNames: 'assets/[name]-[hash].[ext]',

                    manualChunks: (id) => {
                        if (id.includes('node_modules')) {
                            if (id.includes('alpinejs')) return 'vendor-alpine';
                            if (id.includes('chart.js')) return 'vendor-charts';
                            return 'vendor';
                        }
                        if (id.includes('/modules/')) {
                            const match = id.match(/\/modules\/(\w+)\.js$/);
                            if (match) return `module-${match[1]}`;
                        }
                        if (id.includes('offline-manager')) return 'feature-offline';
                        if (id.includes('push-notification')) return 'feature-notifications';
                        if (id.includes('offline-pos')) return 'feature-pos';
                    },
                },
            },

            minify: isLocal ? false : 'terser',
            terserOptions: isLocal ? {} : {
                compress: {
                    drop_console: !isDebug,   // production: hapus console; debug mode: tetap
                    drop_debugger: true,
                    pure_funcs: isDebug ? [] : ['console.info', 'console.debug'],
                    passes: 2,
                },
                format: { comments: false },
            },

            chunkSizeWarningLimit: 500,
            reportCompressedSize: !isLocal,   // hemat waktu build di local
            cssCodeSplit: true,
            sourcemap: isLocal || isDebug,    // sourcemap hanya di local/debug
        },

        optimizeDeps: {
            include: ['alpinejs', 'chart.js', 'dompurify', 'marked'],
        },

        // Server config — hanya relevan saat `npm run dev` (local)
        server: {
            host: '0.0.0.0',
            port: parseInt(env.VITE_PORT || '5173'),
            strictPort: true,
            cors: {
                origin: appUrl,
                credentials: true,
            },
            hmr: {
                host: appHost,
                protocol: 'ws',
                overlay: false,
            },
            watch: {
                usePolling: false,
                interval: 100,
                ignored: [
                    '**/node_modules/**',
                    '**/storage/**',
                    '**/vendor/**',
                    '**/.git/**',
                    '**/bootstrap/cache/**',
                ],
            },
            warmup: {
                clientFiles: [
                    'resources/js/app.js',
                    'resources/css/app.css',
                ],
            },
        },
    };
});
