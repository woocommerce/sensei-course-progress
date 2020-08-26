<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Sensei_Course_Progress {

	/**
	 * The single instance of Sensei_Course_Progress.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  private
	 * @since   1.0.0
	 */
	private $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  private
	 * @since   1.0.0
	 */
	private $_token;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  private
	 * @since   1.0.0
	 */
	private $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  private
	 * @since   1.0.0
	 */
	private $assets_url;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct() {
		$this->_version = SENSEI_COURSE_PROGRESS_VERSION;
		$this->_token   = 'sensei_course_progress';

		$this->assets_dir = trailingslashit( dirname( SENSEI_COURSE_PROGRESS_PLUGIN_FILE ) ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/dist/', SENSEI_COURSE_PROGRESS_PLUGIN_FILE ) ) );

		$this->load_plugin_textdomain();

		register_activation_hook( SENSEI_COURSE_PROGRESS_PLUGIN_FILE, array( $this, 'install' ) );
	} // End __construct()

	/**
	 * Set up all hooks and filters if dependencies are met.
	 */
	public static function init() {
		$instance = self::instance();
		add_action( 'init', array( $instance, 'load_localisation' ), 0 );

		if ( ! Sensei_Course_Progress_Dependency_Checker::are_plugin_dependencies_met() ) {
			return;
		}

		/**
		 * Returns the main instance of Sensei_Course_Progress to prevent the need to use globals.
		 *
		 * @since  1.0.0
		 * @return Sensei_Course_Progress
		 */
		function Sensei_Course_Progress() {
			return Sensei_Course_Progress::instance();
		}

		// Load frontend CSS.
		add_action( 'wp_enqueue_scripts', array( $instance, 'enqueue_styles' ), 10 );

		// Include Widget.
		add_action( 'widgets_init', array( $instance, 'include_widgets' ) );
	}

	/**
	 * Include widgets
	 */
	public function include_widgets() {
		include_once dirname( __FILE__ ) . '/class-sensei-course-progress-widget.php';
		register_widget( 'Sensei_Course_Progress_Widget' );
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles()

	/**
	 * Load plugin localisation.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'sensei-course-progress' , false , dirname( SENSEI_COURSE_PROGRESS_PLUGIN_BASENAME ) . '/languages/' );
	} // End load_localisation()

	/**
	 * Load plugin textdomain.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function load_plugin_textdomain () {
		$domain = 'sensei-course-progress';

		$locale = apply_filters( 'plugin_locale' , get_locale() , $domain );

		load_textdomain( $domain , WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain , FALSE , dirname( SENSEI_COURSE_PROGRESS_PLUGIN_BASENAME ) . '/languages/' );
	} // End load_plugin_textdomain

	/**
	 * Main Sensei_Course_Progress Instance
	 *
	 * Ensures only one instance of Sensei_Course_Progress is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Sensei_Course_Progress()
	 * @return Sensei_Course_Progress instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'sensei-course-progress' ), esc_html( $this->_version ) );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'sensei-course-progress' ), esc_html( $this->_version ) );
	} // End __wakeup()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	}

}
