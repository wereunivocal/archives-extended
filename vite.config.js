//vite.config.mjs
import { defineConfig } from 'vite';
import { resolve } from 'path';
import { fileURLToPath } from 'url';
import { dirname } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

// Entry points
const ADMIN_BUNDLE = resolve(__dirname, 'assets/scripts/admin.js');

// Define where the compiled and minified JavaScript files will be saved
const BUILD_DIR = resolve(__dirname, 'dist');

export default defineConfig({
    root: resolve(__dirname),
    base: '/',
    publicDir: false,
    appType: 'custom',
    resolve: {
        alias: {
            jquery: resolve(__dirname, 'assets/scripts/jquery-shim.cjs'),
        },
    },
    css: {
        devSourcemap: true, // Enable CSS source maps in development
    },
    server: {
        host: 'localhost',
        port: 5173,
        strictPort: true,
        cors: true,
        origin: 'http://localhost:5173',
        hmr: {
            host: 'localhost',
            protocol: 'ws',
            port: 5173,
        },
        watch: {
            usePolling: true,
            interval: 100,
        },
    },
    build: {
        // Off deliberately: source maps are not shipped to users (bin/build-dist.sh
        // and .distignore both exclude *.map), and emitting them here would leave a
        // //# sourceMappingURL comment in the bundle pointing at a file that isn't
        // in the plugin zip — a 404 whenever a visitor opens devtools. The dev
        // server serves its own source maps regardless of this setting.
        sourcemap: false,
        assetsDir: '', // Will save the compiled JavaScript files in the root of the dist folder
        manifest: {
            fileName: '.vite/manifest.json'
        }, // Generate manifest.json file in .vite folder (matches your PHP setup)
        emptyOutDir: true, // Empty the dist folder before building
        outDir: BUILD_DIR,
        rollupOptions: {
            input: {
                admin: ADMIN_BUNDLE,
            },
            output: {
                entryFileNames: '[name].[hash].js',
                chunkFileNames: '[name].[hash].js',
                assetFileNames: '[name].[hash].[ext]',
            },
        },
    },
});
