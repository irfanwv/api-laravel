var gulp = require("gulp");
var elixir = require('laravel-elixir');
var wiredep = require('wiredep');
var config = require('./gulp.config')();
var args = require('yargs').argv;
var $ = require('gulp-load-plugins')({lazy: true});

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

/**
 * Wire-up the bower dependencies
 * @return {Stream}
 */
gulp.task('wiredep', function() {
    // log('Wiring the bower dependencies into the html');

    var wiredep = require('wiredep').stream;
    var options = config.getWiredepDefaultOptions();

    // Only include stubs if flag is enabled
    var js = args.stubs ? [].concat(config.js, config.stubsjs) : config.js;

    return gulp
        .src(config.index)
        .pipe(wiredep(options))
        .pipe(inject(js, '', config.jsOrder))
        .pipe(gulp.dest(config.client));
});

elixir(function (mix)
{
    mix.task('wiredep');
    // mix
    //     .styles([
    //         '../../../node_modules/toastr/build/toastr.min.css'
    //     ])
        
    //     .sass('app.scss')

    //     .scripts([
    //         'jquery/dist/jquery.min.js',
    //         'bootstrap-sass/assets/javascripts/bootstrap.min.js',
    //         'toastr/build/toastr.min.js',
            
    //         // 'angular/angular.min.js',
    //         'angular/angular.js',

    //     ], 'resources/assets/js', 'node_modules')

    //     .scripts([
    //         'all.js',

    //         'app.module.js',
    //         'services/services.module.js',

    //         'services/Auth.js',

    //         'auth/login.controller.js'

    //     ], 'public/js', 'resources/assets/js')
});


/**
 * Inject files in a sorted sequence at a specified inject label
 * @param   {Array} src   glob pattern for source files
 * @param   {String} label   The label name
 * @param   {Array} order   glob pattern for sort order of the files
 * @returns {Stream}   The stream
 */
function inject(src, label, order) {
    var options = {read: false};
    if (label) {
        options.name = 'inject:' + label;
    }

    return $.inject(orderSrc(src, order), options);
}

/**
 * Order a stream
 * @param   {Stream} src   The gulp.src stream
 * @param   {Array} order Glob array pattern
 * @returns {Stream} The ordered stream
 */
function orderSrc (src, order) {
    //order = order || ['**/*'];
    return gulp
        .src(src)
        .pipe($.if(order, $.order(order)));
}