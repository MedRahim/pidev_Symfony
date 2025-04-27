const Encore = require('@symfony/webpack-encore');

// Configure the runtime environment if not already configured by the "encore" command.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore

    // Directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // Public path used by the web server to access the output path
    .setPublicPath('/build')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // Will require an extra script tag for runtime.js
    .enableSingleRuntimeChunk()

    /*
     * FRONTEND FRAMEWORKS & STIMULUS
     * Enable the Stimulus bridge to automatically load controllers.json
     */
    .enableStimulusBridge('./assets/controllers.json')

    /*
     * FEATURE CONFIG
     */
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Enable Babel configuration
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })

    // Enable loading of CSS files imported in JavaScript
    // (Encore does this by default when you import CSS in assets/app.js)

    // Optionally enable Sass/SCSS support
    // .enableSassLoader()

    // Copy Seatchart CSS and JS assets to the build directory
    .copyFiles({
        from: './node_modules/seatchart/dist',
        to: 'seatchart/[name].[ext]',
        pattern: /\.(css|min\.css)$/
    })

    .enableStimulusBridge('./assets/controllers.json') 

;

module.exports = Encore.getWebpackConfig();
