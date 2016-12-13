var elixir = require('laravel-elixir');
require('laravel-elixir-webpack');
/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.sass('app.scss')
        .sass('email.scss')
        .webpack('app.js')
        .copy(
           'node_modules/font-awesome/fonts',
           'public/fonts'
        )
});
