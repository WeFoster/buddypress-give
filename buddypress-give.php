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
	if ( ! bpg_plugins_exist() ) {
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
 * Check if dependent plugins are active.
 *
 * @since 1.0.0
 *
 * @return bool True if both Give and BuddyPress are active, false if not.
 */
function bpg_plugins_exist() {

	if ( class_exists( 'BuddyPress' ) && class_exists( 'Give' ) ) {
		return true;
	} else {
		return false;
	}
}
add_action( 'plugins_loaded', 'bpg_plugins_exist' );

/**
 * Output an admin notice if BuddyPress isn't active.
 *
 * @since 1.0.0
 */
function bpg_admin_notice() {

	if ( ! bpg_plugins_exist() ) {
		?>
		<div class="error">
			<p><?php _e( 'BuddyPress Give requires both Give and BuddyPress to be active.', 'buddypress-give' ); ?></p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'bpg_admin_notice' );