<?php
/*
 * Plugin Name: Sensei Course Progress
 * Version: 1.0.7
 * Plugin URI: https://woocommerce.com/products/sensei-course-progress/
 * Description: Sensei extension that displays the learner's progress in the current course/module in a widget on lesson pages.
 * Author: WooThemes
 * Author URI: https://woocommerce.com/
 * Requires at least: 3.8
 * Tested up to: 4.9
 *
 * @package WordPress
 * @author WooThemes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sensei Detection
 */
function sensei_course_progress_init(){

	if ( ! class_exists('Sensei_Main')) {
		
		add_action( 'admin_notices', 'display_activation_error' );
		add_action( 'network_admin_notices', 'display_activation_error' );
	
	} else {

		require_once( dirname( __FILE__ ) . '/includes/class-sensei-course-progress.php' );

		/**
		 * Returns the main instance of Sensei_Course_Progress to prevent the need to use globals.
		 *
		 * @since  1.0.0
		 * @return object Sensei_Course_Progress
		 */
		function Sensei_Course_Progress() {
			return Sensei_Course_Progress::instance( __FILE__, '1.0.7' );
		}

		Sensei_Course_Progress();

		}
}
add_action( 'plugins_loaded', 'sensei_course_progress_init' );

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'ec0f55d8fa7c517dc1844f5c873a77da', 435833 );

/**
 * Display error message notice in the admin.
 *
 * @param string $message
 */
function display_activation_error( $message ) {
	
	echo '<div class="error">';
	echo '<em>Sensei Course Progress</em> requires Sensei to be installed and activated.';
	echo '</div>';

}
