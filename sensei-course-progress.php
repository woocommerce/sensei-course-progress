<?php
/*
 * Plugin Name: Sensei Course Progress
 * Version: 1.0.8
 * Plugin URI: https://woocommerce.com/products/sensei-course-progress/
 * Description: Sensei extension that displays the learner's progress in the current course/module in a widget on lesson pages.
 * Author: Automattic
 * Author URI: https://automattic.com
 * Requires at least: 3.8
 * Tested up to: 4.9
 *
 * @package WordPress
 * @author Automattic
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

require_once dirname( __FILE__ ) . '/includes/class-sensei-course-progress-dependency-checker.php';

if ( ! Sensei_Course_Progress_Dependency_Checker::are_dependencies_met() ) {
	return;
}

require_once( dirname( __FILE__ ) . '/includes/class-sensei-course-progress.php' );

/**
 * Returns the main instance of Sensei_Course_Progress to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Sensei_Course_Progress
 */
function Sensei_Course_Progress() {
	return Sensei_Course_Progress::instance( __FILE__, '1.0.8' );
}

Sensei_Course_Progress();
