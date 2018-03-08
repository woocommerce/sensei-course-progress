( () => {
	'use strict';

	const cleanCSS = require( 'gulp-clean-css' ),
		del = require( 'del' ),
		env = process.env.NODE_ENV || 'prod',
		gulp = require( 'gulp' ),
		path = require( 'path' ),
		rename = require( 'gulp-rename' ),
		runSequence = require( 'run-sequence' ),
		sass = require( 'gulp-sass' ),
		sourcemaps = require( 'gulp-sourcemaps' ),
		wpi18n = require( 'node-wp-i18n' );

	const paths = {
		css: [ 'assets/css/*.scss' ]
	};

	gulp.task( 'clean', function () {
		return del( [ 'build' ] );
	} );

	gulp.task( 'css', function () {
		return gulp.src( paths.css )
			.pipe( sourcemaps.init() )
			.pipe( sass( {
				errLogToConsole: true,
				outputStyle: 'expanded',
			} ).on( 'error', sass.logError ) )
			.pipe( cleanCSS() )
			.pipe( sourcemaps.write() )
			.pipe( rename( { suffix: '.min' } ) )
			.pipe( gulp.dest( 'build/assets/css' ) )
	} );

	gulp.task( 'php', function() {
		return gulp.src( [ '**/*.php', '!node_modules/**', '!build/**' ] )
			.pipe( gulp.dest( 'build' ) );
	} );

	gulp.task( 'i18n', function( cb ) {
		var options = {
			cwd: process.cwd(),
			domainPath: '/languages',
			exclude: [ 'node_modules/.*', 'build/.*' ],
			mainFile: 'sensei-course-progress.php',
			potComments: '',
			potFilename: 'sensei-course-progress.pot',
			potHeaders: {
				poedit: true,
				'language-team': 'LANGUAGE <EMAIL@ADDRESS>',
				'report-msgid-bugs-to': 'https://github.com/woocommerce/sensei-course-progress/issues',
				'x-poedit-keywordslist': true,
			},
			processPot: function( pot, options ) {
				pot.headers[ 'report-msgid-bugs-to' ] = 'https://github.com/woocommerce/sensei-course-progress/issues';
				pot.headers[ 'language-team' ] = 'LANGUAGE <EMAIL@ADDRESS>';

				var translation, // Exclude meta data from pot.
					excluded_meta = [
						'Plugin Name of the plugin/theme',
						'Plugin URI of the plugin/theme',
						'Author of the plugin/theme',
						'Author URI of the plugin/theme'
					];

				for ( translation in pot.translations[ '' ] ) {
					if ( 'undefined' !== typeof pot.translations[ '' ][ translation ].comments.extracted ) {
						if ( excluded_meta.indexOf( pot.translations[ '' ][ translation ].comments.extracted ) >= 0 ) {
							console.log( 'Excluded meta: ' + pot.translations[ '' ][ translation ].comments.extracted );
							delete pot.translations[ '' ][ translation ];
						}
					}
				}

				return pot;
			},
			type: 'wp-plugin',
			updateTimestamp: true,
			updatePoFiles: false
		};

		options.cwd = path.resolve( process.cwd(), options.cwd );
		options.potFile = options.potFilename;

		wpi18n.makepot( options )
			.then( function( wpPackage ) {
				console.log( 'POT file saved to ' + path.relative( wpPackage.getPath(), wpPackage.getPotFilename() ) );
			})
			.catch( function( error ) {
				console.log( error );
			} )
			.finally( function() {
				cb();
			} );
	} );

	gulp.task( 'languages', [ 'i18n' ], function() {
		return gulp.src( 'languages/*.*' )
			.pipe( gulp.dest( 'build/languages' ) );
	} );

	gulp.task( 'watch', function() {
		gulp.watch( paths.css, [ 'css' ]);
		gulp.watch( [ '**/*.php', '!node_modules/**', '!build/**' ], [ 'php' ]);
		gulp.watch( 'languages/*.*', [ 'languages' ]);
	} );

	gulp.task( 'build', function ( cb ) {
		runSequence( 'clean', [ 'css', 'php', 'languages' ], cb );
	} );

	gulp.task( 'build-dev', function ( cb ) {
		runSequence( 'clean', [ 'css', 'php', 'languages' ], [ 'watch' ], cb );
	});

	gulp.task( 'default', function( cb ) {
		runSequence( 'build', cb );
	} );
} )();
