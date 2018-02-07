<?php
/*
 * Plugin Name: Sensei Course Progress
 * Version: 1.0.6
 * Plugin URI: https://woocommerce.com/products/sensei-course-progress/
 * Description: Sensei extension that displays the learner's progress in the current course/module in a widget on lesson pages.
 * Author: WooThemes
 * Author URI: https://woocommerce.com/
 * Requires at least: 3.8
 * Tested up to: 4.3
 *
 * @package WordPress
 * @author WooThemes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

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
 * Functions used by plugins
 */
if ( ! class_exists( 'WooThemes_Sensei_Dependencies' ) ) {
	require_once( __DIR__ . '/woo-includes/class-woothemes-sensei-dependencies.php' );
}

/**
 * Sensei Detection
 */
if ( ! function_exists( 'is_sensei_active' ) ) {
  function is_sensei_active() {
    return WooThemes_Sensei_Dependencies::sensei_active_check();
  }
}

if( is_sensei_active() ) {

	require_once( __DIR__ . '/includes/class-sensei-course-progress.php' );

	/**
	 * Returns the main instance of Sensei_Course_Progress to prevent the need to use globals.
	 *
	 * @since  1.0.0
	 * @return object Sensei_Course_Progress
	 */
	function Sensei_Course_Progress() {
		return Sensei_Course_Progress::instance( __FILE__, '1.0.6' );
	}

	Sensei_Course_Progress();
}
