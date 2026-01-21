const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const webpack = require('webpack');

const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
  ...defaultConfig,
  output: {
    ...defaultConfig.output,
    path: path.resolve(__dirname, 'dist'), // Set the output path to 'dist'
    filename: 'ow-gutenberg.js', // Set the JS file name to 'ow-gutenberg.js'
  },
  module: {
    ...defaultConfig.module,
    rules: [
      ...defaultConfig.module.rules,
    ],
  },
  plugins: [
    ...defaultConfig.plugins.filter(plugin => !(plugin instanceof MiniCssExtractPlugin)),
    new MiniCssExtractPlugin({
      filename: 'ow-gutenberg.css', // Set the CSS file name to 'ow-gutenberg.css'
    }),
    new RemoveEmptyScriptsPlugin(),
    new webpack.DefinePlugin({
      'process.env.ENABLE_LOGS': JSON.stringify(process.env.ENABLE_LOGS),
    }),
  ],
};
