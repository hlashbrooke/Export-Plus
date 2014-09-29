<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Export_Plus {

	/**
	 * The single instance of Export_Plus.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'export_plus';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Add Export+ page to menu
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );

		// Remove Export page from menu
		add_action( 'admin_menu', array( $this, 'remove_export_page' ) );

		// Process download
		add_action( 'admin_init', array( $this, 'download' ) );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
	} // End __construct ()

	public function add_submenu_page () {
		add_submenu_page( 'tools.php', __( 'Export Plus', 'export-plus' ), __( 'Export Plus', 'export-plus' ), 'export', 'export_plus', array( $this, 'export_page' ) );
	}

	public function remove_export_page () {
		if( apply_filters( $this->_token . '_hide_export', false ) ) {
			remove_submenu_page( 'tools.php', 'export.php' );
		}
	}

	public function export_page () {
		require_once( 'export-template.php' );
	}

	public function download () {
		if( ! isset( $_GET['export_plus_download'] ) ) return;

		require_once( 'export-functions.php' );

		$args = array();
		$args['content'] = array();

		if ( isset( $_GET['content'] ) && count( $_GET['content'] ) > 0 ) {
			foreach( $_GET['content'] as $post_type ) {
				switch( $post_type ) {
					case 'posts': $post_type = 'post'; break;
					case 'pages': $post_type = 'page'; break;
				}
				$args['content'][] = $post_type;
			}
		}

		if ( in_array( 'post', $args['content'] ) ) {
			if ( $_GET['cat'] ) {
				$args['category'] = (int) $_GET['cat'];
			}
		}

		if ( $_GET['post_author'] ) {
			$args['author'] = (int) $_GET['post_author'];
		}

		if ( $_GET['post_start_date'] || $_GET['post_end_date'] ) {
			$args['start_date'] = $_GET['post_start_date'];
			$args['end_date'] = $_GET['post_end_date'];
		}

		if ( $_GET['post_status'] ) {
			$args['status'] = $_GET['post_status'];
		}

		/**
		 * Filter the export args.
		 *
		 * @since 3.5.0
		 *
		 * @param array $args The arguments to send to the exporter.
		 */
		$args = apply_filters( 'export_args', $args );

		export_wp_plus( $args );
		die();
	}

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		if( isset( $_GET['page'] ) && 'export_plus' == $_GET['page'] ) {
			wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
			wp_enqueue_script( $this->_token . '-admin' );
		}
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'export-plus', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'export-plus';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main Export_Plus Instance
	 *
	 * Ensures only one instance of Export_Plus is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Export_Plus()
	 * @return Main Export_Plus instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
