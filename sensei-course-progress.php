<?php
/*
 * Plugin Name: Sensei Course Progress
 * Version: 2.0.0-beta.1
 * Plugin URI: https://woocommerce.com/products/sensei-course-progress/
 * Description: Sensei extension that displays the learner's progress in the current course/module in a widget on lesson pages.
 * Author: Automattic
 * Author URI: https://automattic.com
 * Requires at least: 3.8
 * Tested up to: 4.9
 * Woo: 435833:ec0f55d8fa7c517dc1844f5c873a77da
 *
 * @package WordPress
 * @author Automattic
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

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
	return Sensei_Course_Progress::instance( __FILE__, '2.0.0-beta.1' );
}

Sensei_Course_Progress();
