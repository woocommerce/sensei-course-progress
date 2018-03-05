( () => {
	'use strict';

	const cleanCSS = require( 'gulp-clean-css' ),
		del = require( 'del' ),
		env = process.env.NODE_ENV || 'prod',
		gulp = require( 'gulp' ),
		rename = require( 'gulp-rename' ),
		runSequence = require( 'run-sequence' ),
		sass = require( 'gulp-sass' ),
		sourcemaps = require( 'gulp-sourcemaps' );

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

	gulp.task( 'languages', function() {
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

	gulp.task( 'default', function( cb ) {
		runSequence( 'build', cb );
	} );
} )();
