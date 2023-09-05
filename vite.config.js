import { defineConfig } from 'vite';
import path from 'path';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { viteStaticCopy } from 'vite-plugin-static-copy'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
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
        viteStaticCopy({
            targets: [
                {src: path.join(__dirname, '/resources/images'), dest: path.join(__dirname, '/public')},
                {src: path.join(__dirname, '/resources/favicon'), dest: path.join(__dirname, '/public')},
                {src: path.join(__dirname, '/resources/js/jsnativ/desactivarInspect.js'), dest: path.join(__dirname, '/public/js/')},
            ],
        }),
    ],
});
