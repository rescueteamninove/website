var webpack = require('webpack');
var path = require('path');
var ExtractTextPlugin = require('extract-text-webpack-plugin');
config = {
  entry: {
    'app': './js/main.js',
    'styles': './scss/main.scss'
  },
  output: {
    path: path.resolve(__dirname, '../assets/static/gen'),
    filename: '[name].js',
    publicPath: 'assets/static/gen/'
  },
  devtool: '#cheap-module-source-map',
  resolve: {
    modules: ['node_modules'],
    extensions: ['.js', '.json']
  },
  module: {
    loaders: [
      { test: /\.js$/, exclude: /node_modules/,
        use: 'babel-loader' },
      { test: /\.scss$/,
        use: ExtractTextPlugin.extract( {
          fallback: 'style-loader', use: ['css-loader', 'sass-loader'] } ) },
      { test: /\.css$/,
        use: ExtractTextPlugin.extract(
          'style-loader', 'css-loader') },
      { test: /\.(woff2?|ttf|eot|svg|png|jpe?g|gif)$/,
        use: 'file' }
    ]
  },
  plugins: [
    new ExtractTextPlugin('styles.css', {
      allChunks: true
    }),
    new webpack.optimize.UglifyJsPlugin(),
    new webpack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
      'window.jQuery': 'jquery',
      Popper: ['popper.js', 'default'],
      Tether: 'tether'
    })
  ]
};

module.exports = [
    config,
];
