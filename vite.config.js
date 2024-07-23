import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/jquery.dataTables.min.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
