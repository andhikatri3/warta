import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // Huruf judul di-host sendiri, bukan ditarik dari CDN saat halaman
            // dibuka. Kanvas thumbnail ditangkap menjadi gambar di peramban,
            // dan huruf lintas asal tidak ikut tersalin ke hasil tangkapan.
            fonts: [
                bunny('Plus Jakarta Sans', { weights: [400, 500, 600, 700, 800] }),
                bunny('Fredoka', { weights: [500, 600, 700] }),
                bunny('Anton', { weights: [400] }),
                bunny('Playfair Display', { weights: [700, 900] }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
