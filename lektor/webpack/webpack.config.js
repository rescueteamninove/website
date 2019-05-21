var webpack = require('webpack');
var path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

config = {
  mode: 'production', // 'production'
  entry: {
    'app': './js/main.js',
    'styles': './scss/main.scss',
  },
  output: {
    path: path.resolve(__dirname, '../assets/static/gen'),
    filename: '[name].bundle.js',
  },
  devtool: false,
  resolve: {
    modules: ['node_modules'],
    extensions: ['.js', '.json']
  },
  module: {
    rules: [
      { test: /\.js$/,
        exclude: /node_modules/,
        use: 'babel-loader', },
      { test: /\.(sa|sc|c)ss$/,
        use: [
            { loader: MiniCssExtractPlugin.loader, },
            'css-loader',
            'postcss-loader',
            'sass-loader',
        ],},
      { test: /\.woff(2)?(\?v=[0-9]\.[0-9]\.[0-9])?$/, loader: "url-loader?limit=10000&mimetype=application/font-woff" },
      { test: /\.(ttf|eot|svg|png|jpe?g|gif)$/, use: 'file-loader' },
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: '[name].[hash].css',
      chunkFilename: '[id].[hash].css',
    }),
    new webpack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
      'window.jQuery': 'jquery',
      Popper: ['popper.js', 'default'],
      Tether: 'tether'
    }),
  ],
};

module.exports = config;
