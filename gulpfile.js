( () => {
	'use strict';

	const cleanCSS  = require( 'gulp-clean-css' ),
		del         = require( 'del' ),
		env         = process.env.NODE_ENV || 'prod',
		gulp        = require( 'gulp' ),
		path        = require( 'path' ),
		rename      = require( 'gulp-rename' ),
		runSequence = require( 'run-sequence' ),
		sass        = require( 'gulp-sass' ),
		sourcemaps  = require( 'gulp-sourcemaps' ),
		wpi18n      = require( 'node-wp-i18n' ),
		zip         = require( 'gulp-zip' );

	const paths = {
		css: [ 'assets/css/*.scss' ],
		docs: [ 'changelog.txt', 'README.md' ],
		buildDir: 'build/sensei-course-progress',
		packageZip: 'build/sensei-course-progress.zip',
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
			.pipe( gulp.dest( paths.buildDir + '/assets/css' ) )
	} );

	gulp.task( 'php', function() {
		return gulp.src( [ '**/*.php', '!node_modules/**', '!build/**', '!vendor/**' ] )
			.pipe( gulp.dest( paths.buildDir ) );
	} );

	gulp.task( 'i18n', function( cb ) {
		var options = {
			cwd: process.cwd(),
			domainPath: '/languages',
			exclude: [ 'node_modules/.*', 'build/.*', 'vendor/.*' ],
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
			.pipe( gulp.dest( paths.buildDir + '/languages' ) );
	} );

	gulp.task( 'docs', function() {
		return gulp.src( paths.docs )
			.pipe( gulp.dest( paths.buildDir ) );
	} );

	gulp.task( 'watch', function() {
		gulp.watch( paths.css, [ 'css' ]);
		gulp.watch( [ '**/*.php', '!node_modules/**', '!build/**', '!vendor/**' ], [ 'php' ]);
	} );

	gulp.task( 'build', function ( cb ) {
		runSequence( 'clean', [ 'css', 'php', 'languages', 'docs' ], cb );
	} );

	gulp.task( 'build-dev', function ( cb ) {
		runSequence( 'clean', [ 'css', 'php' ], [ 'watch' ], cb );
	});

	gulp.task( 'zip-package', function() {
		return gulp.src( paths.buildDir + '/**/*', { base: paths.buildDir + '/..' } )
			.pipe( zip( paths.packageZip ) )
			.pipe( gulp.dest( '.' ) );
	} );

	gulp.task( 'package', function( cb ) {
		runSequence( 'build', 'zip-package', cb );
	} );

	gulp.task( 'default', function( cb ) {
		runSequence( 'build', cb );
	} );
} )();
