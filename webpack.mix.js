const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/bookings_vue.js', 'public/js')
   .js('resources/assets/js/notifications_vue.js', 'public/js')
   .js('resources/assets/js/information_vue.js', 'public/js')
   .sass('resources/assets/sass/information.scss', 'public/css')
   .js('resources/assets/js/request_service_vue.js', 'public/js')
   .js('resources/assets/js/booking_engine_vue.js', 'public/js/booking_engine.js')
   .sass('resources/assets/sass/booking_engine.scss', 'public/css')
   .js('resources/assets/js/calendar_vue.js', 'public/js/calendar_vue.js')
   .js('resources/assets/js/e_referral.js', 'public/js')
   .js('resources/assets/js/future_bookings_datatable.js', 'public/js/datatable')
   .js('resources/assets/js/service_booking.js', 'public/js')
   .js('resources/assets/js/view_service_vue.js', 'public/js')
   .js('resources/assets/js/tables.js','public/js')
   .js('resources/assets/js/service_management.js', 'public/js')
   .js('resources/assets/js/outdated_services.js', 'public/js')
   .webpackConfig({
      module: {
         rules: [{
            test: /\.jsx?$/,
            exclude: /(bower_components)/,
            use: [{
               loader: 'babel-loader',
               options: Config.babel()
            }]
         }]
      }
   });

