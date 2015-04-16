<?php
/**
 * Plugin Name: BuddyPress Give
 * Plugin URI: https://github.com/CFCommunity-net/buddypress-give
 * Description: BuddyPress Give is a Give extension created for CFCommunity.net
 * Version: 1.0.0
 * Contributors: bowefrankema, henry.wright
 * Text Domain: buddypress-give
 * Domain Path: /languages/
 */

/**
 * BuddyPress Give
 *
 * @package BuddyPress Give
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Initialise the plugin.
 *
 * @since 1.0.0
 */
function bpg_init() {

	// Bail if Give or BuddyPress aren't active.
	if ( ! bpg_req_plugins_exist() ) {
		return;
	}

	// Bail if the required components aren't active.
	if ( ! bp_is_active( 'activity' ) ) {
		return;
	}

	/**
	 * Require the plugin's classes.
	 */
	require dirname( __FILE__ ) . '/inc/classes.php';

	/**
	 * Require the plugin's functions.
	 */
	require dirname( __FILE__ ) . '/inc/functions.php';

	/**
	 * Require the plugin's admin.
	 */
	require dirname( __FILE__ ) . '/inc/admin.php';

	/**
	 * Require the plugin's widgets.
	 */
	require dirname( __FILE__ ) . '/inc/widgets.php';
}
add_action( 'bp_include', 'bpg_init' );

/**
 * Load the plugin's textdomain.
 * 
 * @since 1.0.0
 */
function bpg_i18n() {

	load_plugin_textdomain( 'buddypress-give', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'bpg_i18n' );

/**
 * Enqueue the JS.
 *
 * @since 1.0.0
 */
function bpg_enqueue_js() {

	// Bail if Give or BuddyPress aren't active.
	if ( ! bpg_req_plugins_exist() ) {
		return;
	}

	// Bail if the required components aren't active.
	if ( ! bp_is_active( 'activity' ) ) {
		return;
	}

	// Bail if the user isn't logged in.
	if ( ! is_user_logged_in() )
		return;

	wp_enqueue_script( 'bpg-script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ), NULL, true );
}
add_action( 'wp_enqueue_scripts', 'bpg_enqueue_js' );

/**
 * Enqueue the CSS.
 *
 * @since 1.0.0
 */
function bpg_enqueue_css() {

	// Bail if Give or BuddyPress aren't active.
	if ( ! bpg_req_plugins_exist() ) {
		return;
	}

	// Bail if the required components aren't active.
	if ( ! bp_is_active( 'activity' ) ) {
		return;
	}

	wp_register_style( 'buddypress-give', plugins_url( 'css/style.css', __FILE__ ) );

	wp_enqueue_style( 'buddypress-give' );
}
add_action( 'wp_enqueue_scripts', 'bpg_enqueue_css' );

/**
 * Check if required plugins are active.
 *
 * @since 1.0.0
 *
 * @return bool True if both Give and BuddyPress are active, false if not.
 */
function bpg_req_plugins_exist() {

	if ( class_exists( 'BuddyPress' ) && class_exists( 'Give' ) ) {
		return true;
	} else {
		return false;
	}
}
add_action( 'plugins_loaded', 'bpg_req_plugins_exist' );

/**
 * Output an admin notice if BuddyPress isn't active.
 *
 * @since 1.0.0
 */
function bpg_admin_notice() {

	if ( ! bpg_req_plugins_exist() ) {
		?>
		<div class="error">
			<p><?php _e( 'BuddyPress Give requires both Give and BuddyPress to be active.', 'buddypress-give' ); ?></p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'bpg_admin_notice' );