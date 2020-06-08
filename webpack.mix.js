const mix = require('laravel-mix')

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

// Add rule to pipe every imported *.worker.js file through workerize-loader
// and (for a lack of being able to declare worker output files) clean up old
// worker files
const anymatch = require('anymatch')
const { CleanWebpackPlugin } = require('clean-webpack-plugin')

mix.extend('handleWorkers', webpackConfig => {
  webpackConfig.plugins.push(
    new CleanWebpackPlugin({
      cleanOnceBeforeBuildPatterns: ['*.worker.js'],
    }),
  )

  const originalJsRule = webpackConfig.module.rules.find(rule =>
    anymatch(rule.test, 'file.js'),
  )

  webpackConfig.module.rules.unshift({
    ...originalJsRule,
    test: /\.worker\.js$/,
    use: ['workerize-loader', ...originalJsRule.use],
  })
})

mix
  .js('resources/js/app.js', 'public/js')
  .js('resources/js/helpers/toast.js', 'public/js')
  .sass('resources/sass/stylesheet.scss', 'public/css')
  .sass('resources/sass/toast.scss', 'public/css')
  .sass('resources/sass/package-readme.scss', 'public/css')
  .sass('resources/sass/package-versions.scss', 'public/css')
  .sass('resources/sass/package-relations.scss', 'public/css')
  .styles(
    [require.resolve('modern-normalize')],
    'public/css/modern-normalize.css',
  )
  .sourceMaps()
  .copyDirectory('resources/fonts', 'public/fonts')
  .handleWorkers()

if (mix.inProduction()) {
  mix.version()
}
