<?php
/*
 * Plugin Name: Export Plus
 * Version: 1.0
 * Plugin URI: https://wordpress.org/plugins/export-plus/
 * Description: A greatly improved export tool for your WordPress site.
 * Author: Hugh Lashbrooke
 * Author URI: http://www.hughlashbrooke.com/
 * Requires at least: 4.0
 * Tested up to: 4.1
 *
 * @package WordPress
 * @author Hugh Lashbrooke
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-export-plus.php' );

/**
 * Returns the main instance of Export_Plus to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Export_Plus
 */
function Export_Plus () {
	$instance = Export_Plus::instance( __FILE__, '1.0.0' );
	return $instance;
}

Export_Plus();
