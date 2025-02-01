const path = require('path');

const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const WooCommerceDependencyExtractionWebpackPlugin = require('@woocommerce/dependency-extraction-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

// Remove SASS rule from the default config so we can define our own.
const defaultRules = defaultConfig.module.rules.filter((rule) => {
	return String(rule.test) !== String(/\.(sc|sa)ss$/);
});

module.exports = {
  ...defaultConfig,
  mode: 'development',
  entry: {
  	'giveaway-product/frontend': path.resolve(process.cwd(), 'src/giveaway-product', 'frontend.js'),
  	
  	'auto-coupon/frontend': path.resolve(process.cwd(), 'src/auto-coupon', 'frontend.js'),
  	
		'main/index': path.resolve(process.cwd(), 'src/main', 'index.js'),
		'main/frontend': path.resolve(process.cwd(), 'src/main', 'frontend.js'),
	},
	output:{
    filename: "[name].js",
    path: path.resolve(__dirname, "build")
  },
  plugins: [
		...defaultConfig.plugins.filter(
			( plugin ) =>
				plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new WooCommerceDependencyExtractionWebpackPlugin()
	],
};
