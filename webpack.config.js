const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const RtlCssPlugin = require( '@wordpress/scripts/plugins/rtlcss-webpack-plugin' );
const { getWebpackEntryPoints } = require( '@wordpress/scripts/utils/config' );

const projectRoot = process.cwd();

// Объединяем entry-точки блоков из block.json с главным main.js
const blockEntries = typeof getWebpackEntryPoints( 'script' ) === 'function'
	? getWebpackEntryPoints( 'script' )()
	: getWebpackEntryPoints( 'script' );

const config = {
	...defaultConfig,
	entry: {
		...blockEntries,
		main: path.resolve( projectRoot, 'src/js/main.js' ),
		editor: path.resolve( projectRoot, 'src/js/editor.js' ),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( projectRoot, 'build' ),
		filename: '[name].js',
	},
};

// Вывод CSS в правильные папки, отключаем RTL
config.plugins = defaultConfig.plugins
	.filter( ( plugin ) => ! ( plugin instanceof RtlCssPlugin ) )
	.map( ( plugin ) => {
		if ( plugin instanceof MiniCssExtractPlugin ) {
			return new MiniCssExtractPlugin( {
			filename: ( pathData ) => {
				const name = pathData.chunk.name;
				// Главный файл и редактор - в styles/
				if ( name === 'main' || name === 'editor' ) {
					return `styles/${ name }.css`;
				}
				// Если это блок - оставляем путь как есть
				if ( name.startsWith( 'blocks/' ) ) {
					return `${ name }.css`;
				}
				return '[name].css';
			},
			} );
		}
		return plugin;
	} );

// Копирование картинок, иконок и блоков из src в build.
// Шрифты подключаются через @font-face в SCSS и обрабатываются asset-модулем webpack.
config.plugins.push(
	new CopyWebpackPlugin( {
		patterns: [
			{
				from: '**/*',
				context: path.resolve( projectRoot, 'src/img' ),
				to: 'img',
				noErrorOnMissing: true,
			},
			{
				from: '**/*',
				context: path.resolve( projectRoot, 'src/icons' ),
				to: 'icons',
				noErrorOnMissing: true,
			},
			{
				from: '**/*',
				context: path.resolve( projectRoot, 'src/blocks' ),
				to: 'blocks',
				noErrorOnMissing: true,
				globOptions: {
					ignore: [ '**/*.js', '**/*.scss' ],
				},
			},
		],
	} )
);

module.exports = config;
