<?php
/**
 * Plugin Name:       Personalizowana maseczka
 * Description:       Mała wtyczka umożliwiająca stworzenie modyfikowalnego produktu w WooCommerce.
 * Plugin URI:        https://github.com/s16945/cms
 * Version:           1.12.3
 * Author:            Lukasz Gajewski, Marcin Pejski, Michal Karczmarczyk, Krzysztof Żebrowski
 * Author URI:        pja.edu.pl
 * Requires at least: 3.0.0
 * Tested up to:      4.4.2
 *
 * @package noPackage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Class
 *
 */
final class Theme_Customisations {

	/**
	 * Set up the plugin
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'theme_customisations_setup' ), -1 );
		require_once( 'custom/functions.php' );
	}

	/**
	 * Setup all the things
	 */
	public function theme_customisations_setup() {
		add_action( 'wp_enqueue_scripts', array( $this, 'theme_customisations_css' ), 999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'theme_customisations_js' ) );
		add_filter( 'template_include',   array( $this, 'theme_customisations_template' ), 11 );
		add_filter( 'wc_get_template',    array( $this, 'theme_customisations_wc_get_template' ), 11, 5 );
	}

	/**
	 * Enqueue the CSS
	 *
	 * @return void
	 */
	public function theme_customisations_css() {
		wp_enqueue_style( 'custom-css', plugins_url( '/custom/style.css', __FILE__ ) );
	}

	/**
	 * Enqueue the Javascript
	 *
	 * @return void
	 */
	public function theme_customisations_js() {
		wp_enqueue_script( 'custom-js', plugins_url( '/custom/custom.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script('fabric', plugins_url('/custom/assets/fabric.min.js'));
	}

	public function theme_customisations_template( $template ) {
		if ( file_exists( untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/custom/templates/' . basename( $template ) ) ) {
			$template = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/custom/templates/' . basename( $template );
		}

		return $template;
	}

	public function theme_customisations_wc_get_template( $located, $template_name, $args, $template_path, $default_path ) {
		$plugin_template_path = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/custom/templates/woocommerce/' . $template_name;

		if ( file_exists( $plugin_template_path ) ) {
			$located = $plugin_template_path;
		}

		return $located;
	}
} // End Class

/**
 * The 'main' function
 *
 * @return void
 */
function theme_customisations_main() {
	new Theme_Customisations();
}

/**
 * Initialize the plugin
 */
add_action( 'plugins_loaded', 'theme_customisations_main' );
