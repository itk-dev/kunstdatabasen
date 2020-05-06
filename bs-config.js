
/*
 |--------------------------------------------------------------------------
 | Browser-sync config file
 |--------------------------------------------------------------------------
 |
 | For up-to-date information about the options:
 |   http://www.browsersync.io/docs/options/
 |
 | There are more options than you see here, these are just the ones that are
 | set internally. See the website for more info.
 |
 | Install browsersync
 | Global: `npm install -g browser-sync`
 | Local: `npm install browser-sync --save-dev`
 |
 | Start syncing by running this command from your the root of your project
 | `browser-sync start --config bs-config.js`
 |
 */
module.exports = {
    ui: false,
    notify: false,
    files: [
      'templates/**/*.twig',
      'assets/**/*.*'
    ],
    proxy: 'kunstdatabasen.local.itkdev.dk'
};