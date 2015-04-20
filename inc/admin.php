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
 * Add sub menu items(s).
 *
 * @since 1.0.0
 */
function bpg_add_submenu() {

	// Add a BuddyPress sub menu page.
	add_submenu_page(
		'edit.php?post_type=give_forms',
		'Give Settings: BuddyPress',
		'BuddyPress',
		'manage_options',
		'give-buddypress',
		'bpg_settings_page'
	);

	// Add a Badges sub menu page.
	add_submenu_page(
		'edit.php?post_type=give_forms',
		'Give Settings: Badges',
		'Badges',
		'manage_options',
		'give-badges',
		'bpg_badges_page'
	);
}
add_action( 'admin_menu', 'bpg_add_submenu' );

/**
 * Output content for the BuddyPress page.
 *
 * @since 1.0.0
 */
function bpg_settings_page() {

	?>
	<div class="wrap">

		<h2><?php _e( 'BuddyPress', 'buddypress-give' ); ?></h2>
		<?php settings_errors(); ?>
		<p><?php _e( 'The settings on this page relate to BuddyPress.', 'buddypress-give' ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'give-buddypress' ); ?>
			<?php do_settings_sections( 'give-buddypress' ); ?>           
			<?php submit_button(); ?>
		</form>

	</div>
	<?php
}

/**
 * Output content for the Badges page.
 *
 * @since 1.0.0
 */
function bpg_badges_page() {
	?>
	<div class="wrap">
		<h2><?php _e( 'Badges', 'buddypress-give' ); ?></h2>
		<p><?php _e( 'Set the badges members receive when they have donated over a given amount.', 'buddypress-give' ); ?></p>

		<?php cmb2_metabox_form( '_give_badges_metabox', 'bpg-badges' ); ?>
	</div>
	<?php
}

/**
 * Add a cmb2 metabox to the Badges page.
 *
 * @since 1.0.0
 */
function bpg_add_options_page_metabox() {

	$prefix = '_give_';

	$cmb_group = new_cmb2_box( array(
		'id' => $prefix . 'badges_metabox',
		'show_on' => array(
			'key' => 'options-page',
			'value' => array( 'bpg-badges' )
		)
	) );

	$group_field_id = $cmb_group->add_field( array(
		'id' => $prefix . 'badges',
		'type' => 'group',
		'options' => array(
			'group_title' => __( 'Badge {#}', 'buddypress-give' ),
			'add_button' => __( 'Add another badge', 'buddypress-give' ),
			'remove_button' => __( 'Remove this badge', 'buddypress-give' ),
			'sortable' => true
		)
	) );

	$cmb_group->add_group_field( $group_field_id, array(
		'name' => __( 'Description', 'buddypress-give' ),
		'id' => $prefix . 'text',
		'type' => 'text'
	) );

	$cmb_group->add_group_field( $group_field_id, array(
		'name' => __( 'Amount', 'buddypress-give' ),
		'id' => $prefix . 'amount',
		'type' => 'text_money',
		'before_field' => give_currency_symbol(),
		'attributes' => array(
			'placeholder' => give_format_amount( '0.00' )
		)
	) );

	$cmb_group->add_group_field( $group_field_id, array(
		'name' => __( 'Image', 'buddypress-give' ),
		'id' => $prefix . 'image',
		'type' => 'file',
		'preview_size' => array( 60, 60 ),
		'options' => array(
			'url' => true,
			'add_upload_file_text' => __( 'Upload or choose badge', 'buddypress-give' )
		)
	) );
}
add_action( 'cmb2_init', 'bpg_add_options_page_metabox' );

/**
 * Register the settings.
 *
 * @since 1.0.0
 */
function bpg_register_admin_settings() {

	$defaults = array(
		'bpg-vis' => '',
		'bpg-show-amount' => '',
		'bpg-default-message' => '',
		'bpg-form-id' => ''
	);

	// Check the option exists.
	if ( false == get_blog_option( get_current_blog_id(), 'bpg-options' ) ) {  
		add_blog_option( get_current_blog_id(), 'bpg-options', $defaults );
	}

	// Add a settings section.
	add_settings_section(
		'bpgive',
		'',
		'bpg_settings_section_callback',
		'give-buddypress'
	);

	// Add a settings field.
	add_settings_field(
		'bpg-vis',
		__( 'Donation visibility', 'buddypress-give' ),
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
		__( 'Donation amount', 'buddypress-give' ),
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
		__( 'Default donation message', 'buddypress-give' ),
		'bpg_settings_field_callback_default_text',
		'give-buddypress',
		'bpgive'
	);

	// Add a settings field.
	add_settings_field(
		'bpg-form-id',
		__( 'Donation form ID', 'buddypress-give' ),
		'bpg_settings_field_callback_form_id',
		'give-buddypress',
		'bpgive'
	);

	// Register a setting.
	register_setting(
		'give-buddypress',
		'bpg-options'
	);

	// Register a setting.
	register_setting( 
		'bpg-badges', 
		'bpg-badges' 
	);
}
add_action( 'admin_init', 'bpg_register_admin_settings', 99 );

/**
 * Fill the section with content.
 *
 * @since 1.0.0
 */
function bpg_settings_section_callback() {}

/**
 * Add an input to the field.
 *
 * @since 1.0.0
 */
function bpg_settings_field_callback_vis( $args ) {

	$options = get_blog_option( get_current_blog_id(), 'bpg-options' );
	?>
	<input type="checkbox" name="bpg-options[bpg-vis]" id="bpg-vis" value="1" <?php checked( 1, isset( $options['bpg-vis'] ) ? $options['bpg-vis'] : '' ); ?> />
	<label for="bpg-vis"><?php echo $args[0]; ?></label>
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
	<input type="checkbox" name="bpg-options[bpg-show-amount]" id="bpg-show-amount" value="1" <?php checked( 1, isset( $options['bpg-show-amount'] ) ? $options['bpg-show-amount'] : '' ); ?> />
	<label for="bpg-show-amount"><?php echo $args[0]; ?></label>
	<?php
}

/**
 * Add an input to the field.
 *
 * @since 1.0.0
 */
function bpg_settings_field_callback_default_text( $args ) {

	$options = get_blog_option( get_current_blog_id(), 'bpg-options' );

	$options['bpg-default-message'] = isset( $options['bpg-default-message'] ) ? $options['bpg-default-message'] : '';

	?>
	<textarea name="bpg-options[bpg-default-message]" id="bpg-default-message" class="large-text" rows="3"><?php echo $options['bpg-default-message']; ?></textarea>
	<p class="description">This message will be used if the donor doesn't provide a custom message.</p>
	<?php
}

/**
 * Add an input to the field.
 *
 * @since 1.0.0
 */
function bpg_settings_field_callback_form_id( $args ) {

	$options = get_blog_option( get_current_blog_id(), 'bpg-options' );

	$options['bpg-form-id'] = isset( $options['bpg-form-id'] ) ? $options['bpg-form-id'] : '';

	?>
	<input type="text" name="bpg-options[bpg-form-id]" id="bpg-form-id" value="<?php echo $options['bpg-form-id']; ?>" />
	<p class="description">This donation form will be displayed on profile pages.</p>
	<?php
}