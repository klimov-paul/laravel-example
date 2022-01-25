const mix = require('laravel-mix');
const path = require('path');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */
mix.webpackConfig({
    resolve: {
        alias: {
            '@': path.resolve('resources/js'),
        },
    },
});

mix.js('resources/js/app.js', 'public/js').vue()
    .sass('resources/scss/app.scss', 'public/css/app.css');

if (mix.inProduction()) {
    mix.version();
}
