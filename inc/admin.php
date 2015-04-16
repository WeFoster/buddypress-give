<?php
/**
 * Admin functions
 *
 * @package BuddyPress Give
 * @subpackage Admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Add a submenu under Donations.
 *
 * @since 1.0.0
 */
function bpg_add_submenu() {

	// Add a submenu page.
	add_submenu_page(
		'edit.php?post_type=give_forms',
		'Give Settings: BuddyPress',
		'BuddyPress',
		'administrator',
		'give-buddypress',
		'bpg_settings_page'
	);
}
add_action( 'admin_menu', 'bpg_add_submenu' );

/**
 * Output the content for the page.
 *
 * @since 1.0.0
 */
function bpg_settings_page() {

	?>
	<div class="wrap">

	<div id="icon-themes" class="icon32"></div>

	<h2><?php _e( 'BuddyPress', 'buddypress-give' ); ?></h2>

	<?php settings_errors(); ?>

	<p><?php _e( 'Page text to be added.', 'buddypress-give' ); ?></p>

	<form method="post" action="options.php">
		<?php settings_fields( 'give-buddypress' ); ?>
		<?php do_settings_sections( 'give-buddypress' ); ?>           
		<?php submit_button(); ?>
	</form>

	</div>
	<?php
}

/**
 * Register the settings.
 *
 * @since 1.0.0
 */
function bpg_register_admin_settings() {

	$defaults = array(
		'bpg-vis' => '',
		'bpg-show-amount' => '',
		'bpg-default-message' => ''
	);

	// Check the option exists.
	if ( false == get_blog_option( get_current_blog_id(), 'bpg-options' ) ) {  
		add_blog_option( 'bpg-options', $defaults );
	}

	// Add a settings section.
	add_settings_section(
		'bpgive',
		__( 'Activity stream', 'buddypress-give' ),
		'bpg_settings_section_callback',
		'give-buddypress'
	);

	// Add a settings field.
	add_settings_field(
		'bpg-vis',
		__( 'Label 1', 'buddypress-give' ),
		'bpg_settings_field_callback_vis',
		'give-buddypress',
		'bpgive',
		array(								
			__( 'Hide from site-wide activity stream?', 'buddypress-give' )
		)
	);

	// Add a settings field.
	add_settings_field(
		'bpg-show-amount',
		__( 'Label 2', 'buddypress-give' ),
		'bpg_settings_field_callback_amount',
		'give-buddypress',
		'bpgive',
		array(								
			__( 'Show amount donated?', 'buddypress-give' )
		)
	);

	// Add a settings field.
	add_settings_field(
		'bpg-default-message',
		__( 'Label 3', 'buddypress-give' ),
		'bpg_settings_field_callback_default_text',
		'give-buddypress',
		'bpgive'
	);

	// Register a setting.
	register_setting(
		'give-buddypress',
		'bpg-options'
	);
}
add_action( 'admin_init', 'bpg_register_admin_settings', 99 );

/**
 * Fill the section with content.
 *
 * @since 1.0.0
 */
function bpg_settings_section_callback() {
	echo '<p>' . __( 'Section text to be added.', 'buddypress-give' ) . '</p>';
}

/**
 * Add an input to the field.
 *
 * @since 1.0.0
 */
function bpg_settings_field_callback_vis( $args ) {

	$options = get_blog_option( get_current_blog_id(), 'bpg-options' );
	?>
	<input type="checkbox" name="bpg-options[bpg-vis]" id="bpg-vis" value="1" <?php checked( 1, isset( $options['bpg-vis'] ) ? $options['bpg-vis'] : 0 ); ?> />
	<label for="bpg-vis"><?php echo $args[0]; ?></label>
	<p class="description">In a few words, explain what this option is about.</p>
	<?php
}

/**
 * Add an input to the field.
 *
 * @since 1.0.0
 */
function bpg_settings_field_callback_amount( $args ) {

	$options = get_blog_option( get_current_blog_id(), 'bpg-options' );
	?>
	<input type="checkbox" name="bpg-options[bpg-show-amount]" id="bpg-show-amount" value="1" <?php checked( 1, isset( $options['bpg-show-amount'] ) ? $options['bpg-show-amount'] : 0 ); ?> />
	<label for="bpg-show-amount"><?php echo $args[0]; ?></label>
	<p class="description">In a few words, explain what this option is about.</p>
	<?php
}

/**
 * Add an input to the field.
 *
 * @since 1.0.0
 */
function bpg_settings_field_callback_default_text( $args ) {

	$options = get_blog_option( get_current_blog_id(), 'bpg-options' );

	$message = isset( $options['bpg-default-message'] ) ? $options['bpg-default-message'] : '';

	?>
	<input type="text" name="bpg-options[bpg-default-message]" id="bpg-default-message" value="<?php echo $message; ?>" />
	<p class="description">In a few words, explain what this option is about.</p>
	<?php
}