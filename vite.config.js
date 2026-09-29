import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Space Grotesk', {
                    weights: [500, 600, 700],
                    preload: [{ weight: 700, style: 'normal' }],
                }),
                bunny('Inter', {
                    weights: [400, 500, 600, 700],
                    preload: [{ weight: 400, style: 'normal' }],
                }),

                /* The Latin faces carry no Bengali glyphs, so Bangla would fall
                   back to whatever the reader's system happens to have. These two
                   cover it: Hind Siliguri for text, Anek Bangla for display.
                   Only the bengali subset is downloaded — Latin still comes from
                   Inter and Space Grotesk, chosen per glyph by the browser.
                   Noto draws the Bengali digits the conventional way, which
                   matters: ১ in some faces is easily read as ৲. */
                bunny('Noto Sans Bengali', {
                    weights: [400, 500, 600, 700],
                    subsets: ['bengali'],
                    preload: [{ weight: 400, style: 'normal' }],
                }),
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
