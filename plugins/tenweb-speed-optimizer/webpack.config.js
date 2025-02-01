const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'block-tenwebspeedoptimizer-sidebarpluginblock': './assets/js/sidebar-plugin.js'
    },
    output: {
        path: path.join(__dirname, './assets/js/gutenberg'),
        filename: 'sidebar-plugin-compiled.js'
    },
}