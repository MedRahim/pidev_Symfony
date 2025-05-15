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

    // ENTRY CONFIG
    .addEntry('app', './assets/app.js')

    // Split files for optimization
    .splitEntryChunks()

    // Single runtime chunk for efficient caching
    .enableSingleRuntimeChunk()

    // Enable Stimulus bridge for frontend frameworks
    .enableStimulusBridge('./assets/controllers.json')

    // Feature configuration
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Configure Babel for polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })

    // Copy Seatchart assets to the build directory
    .copyFiles({
        from: './node_modules/seatchart/dist',
        to: 'seatchart/[name].[ext]',
        pattern: /\.(css|min\.css)$/
    });

module.exports = Encore.getWebpackConfig();
