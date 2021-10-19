<?php
/*
 * Plugin Name: Sensei LMS Course Progress
 * Version: 2.0.4
 * Plugin URI: https://woocommerce.com/products/sensei-course-progress/
 * Description: Sensei LMS extension that displays the student's progress in the current course/module in a widget on lesson pages.
 * Author: Automattic
 * Author URI: https://automattic.com
 * Requires at least: 5.6
 * Requires PHP: 7.0
 * Tested up to: 5.8
 *
 * @package WordPress
 * @author Automattic
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SENSEI_COURSE_PROGRESS_VERSION', '2.0.4' );
define( 'SENSEI_COURSE_PROGRESS_PLUGIN_FILE', __FILE__ );
define( 'SENSEI_COURSE_PROGRESS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once dirname( __FILE__ ) . '/includes/class-sensei-course-progress-dependency-checker.php';

if ( ! Sensei_Course_Progress_Dependency_Checker::are_system_dependencies_met() ) {
	return;
}

require_once dirname( __FILE__ ) . '/includes/class-sensei-course-progress.php';

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', array( 'Sensei_Course_Progress', 'init' ), 5 );

Sensei_Course_Progress::instance();
