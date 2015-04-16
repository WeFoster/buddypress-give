<?php
/**
 * User defined functions
 *
 * @package BuddyPress Give
 * @subpackage Functions
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Output a custom form field.
 * 
 * @since 1.0.0
 */
function bpg_custom_form_fields( $form_id ) {

	?>
	<div id="give-message-wrap">
		<label class="give-label" for="give-message"><?php _e( 'Custom donation message', 'buddypress-give' ); ?></label>
		<span class="give-tooltip icon icon-question" data-tooltip="<?php _e( 'Please enter a custom donation message', 'buddypress-give' ) ?>"></span>

		<textarea class="give-textarea" name="give_message" id="give-message"></textarea>
	</div>
	<?php
}
add_action( 'give_after_donation_levels', 'bpg_custom_form_fields', 10, 1 );

/**
 * Validate the custom form field.
 *
 * @since 1.0.0
 *
 * @param array $valid_data
 * @param array $data
 */
function bpg_validate_custom_fields( $valid_data, $data ) {

	if ( empty( $data['give_message'] ) ) {
		give_set_error( 'give_message', __( 'Please enter a custom donation message.', 'buddypress-give' ) );
	}
}
// add_action( 'give_checkout_error_checks', 'bpg_validate_custom_fields', 10, 2 );

/**
 * Add data to payment meta.
 * 
 * @since 1.0.0
 *
 * @param array $payment_meta
 * @return mixed
 */
function bpg_store_custom_field_data( $payment_meta ) {
	$payment_meta['message'] = isset( $_POST['give_message'] ) ? implode( "n", array_map( 'sanitize_text_field', explode( "n", $_POST['give_message'] ) ) ) : '';

	return $payment_meta;
}
add_filter( 'give_payment_meta', 'bpg_store_custom_field_data' );

/**
 * Show data in transaction details.
 * 
 * @since 1.0.0
 *
 * @param array $payment_meta
 * @param array $user_info
 */
function bpg_purchase_details( $payment_meta, $user_info ) {

	// Bail if no data exists for this transaction.
	if ( ! isset( $payment_meta['message'] ) ) {
		return;
	}

	?>
	<div class="message-data">
	<label><?php echo __( 'Message', 'buddypress-give' ); ?></label>
	<?php echo wpautop( $payment_meta['message'] ); ?>
	</div>
	<?php
}
add_action( 'give_payment_personal_details_list', 'bpg_purchase_details', 10, 2 );

/**
 * Add an activity item.
 * 
 * @since 1.0.0
 *
 * @param int $payment_id The ID of the payment.
 */
function bpg_add_activity_item( $payment_id ) {

	// Get payment meta.
	$payment_meta = give_get_payment_meta( $payment_id );

	// Get payment total.
	$payment_total = give_get_payment_meta( $payment_id, '_give_payment_total' );

	// Get option data.
	$option_data = get_blog_option( get_current_blog_id(), 'bpg-options' );

	// Get donor info.
	$user = get_userdata( $payment_meta['user_info']['id'] );

	if ( $option_data['bpg-show-amount'] ) {
		$action = sprintf( __( '%s just donated %s%1.2f to %s', 'buddypress-give' ), $user->display_name, give_currency_symbol(), (float) $payment_total, get_the_title( $payment_meta['form_id'] ) );
	} else {
		$action = sprintf( __( '%s just donated to %s', 'buddypress-give' ), $user->display_name, get_the_title( $payment_meta['form_id'] ) );
	}

	$content = empty( $payment_meta['message'] ) ? $option_data['bpg-default-message'] : $payment_meta['message'];

	$component = 'activity';

	$type = 'give_donation';

	$primary_link = bp_core_get_userlink( $user->ID, false, true );

	$user_id = $user->ID;

	$item_id = $payment_meta['form_id'];

	$hide_sitewide = empty( $option_data['bpg-vis'] ) ? false : true;

	bp_activity_add( array(
		'action' => $action,
		'content' => $content,
		'component' => $component,
		'type' => $type,
		'primary_link' => $primary_link,
		'user_id' => $user_id,
		'item_id' => $item_id,
		'hide_sitewide' => $hide_sitewide
	) );
}
add_action( 'give_complete_purchase', 'bpg_add_activity_item' );