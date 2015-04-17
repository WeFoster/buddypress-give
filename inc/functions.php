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
 * Output custom form field(s).
 * 
 * @since 1.0.0
 */
function bpg_custom_form_fields( $form_id ) {

	// Get user data.
	$user = get_userdata( bp_loggedin_user_id() );
	?>
	<div id="give-message-wrap">
		<label class="give-label" for="give-message"><?php _e( 'Custom donation message', 'buddypress-give' ); ?></label>
		<span class="give-tooltip icon icon-question" data-tooltip="<?php _e( 'Please enter a custom donation message', 'buddypress-give' ) ?>"></span>

		<textarea class="give-textarea" name="give_message" id="give-message"></textarea>
	</div>

	<!-- Pass user email in a hidden field -->
	<input type="hidden" name="give_email" value="<?php echo $user->user_email; ?>">
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
 * Add donation message to the purchase summary shortcode output.
 * 
 * @since 1.0.0
 *
 * @param object $payment An object of type WP_Post
 */
function bpg_purchase_summary( $payment ) {

	$payment_meta = give_get_payment_meta( $payment->ID );

	if ( $payment_meta['message'] ) {
		?>
		<tr>
		<td><strong><?php _e( 'Message', 'buddypress-give' ); ?>:</strong></td>
		<td><?php echo $payment_meta['message']; ?></td>
		</tr>
		<?php
	}
}
add_action( 'give_payment_receipt_after', 'bpg_purchase_summary' );

/**
 * Add an item to the activity stream.
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

	$primary_link = bp_core_get_userlink( $user->ID, false, true );

	$donor = '<a href="' . $primary_link . '" title="' . $user->display_name . '">' . $user->display_name . '</a>';

	switch ( $option_data['bpg-show-amount'] ) {
		case "1":
			$action = sprintf( __( '%s just donated %s%1.2f to %s', 'buddypress-give' ), $donor, give_currency_symbol(), (float) $payment_total, get_the_title( $payment_meta['form_id'] ) );
			break;
		default:
			$action = sprintf( __( '%s just donated to %s', 'buddypress-give' ), $donor, get_the_title( $payment_meta['form_id'] ) );
	}

	// Add an item to the activity stream.
	bp_activity_add( array(
		'action' => $action,
		'content' => empty( $payment_meta['message'] ) ? $option_data['bpg-default-message'] : $payment_meta['message'],
		'component' => 'activity',
		'type' => 'give_donation',
		'primary_link' => $primary_link,
		'user_id' => $user->ID,
		'item_id' => $payment_meta['form_id'],
		'hide_sitewide' => empty( $option_data['bpg-vis'] ) ? false : true
	) );
}
add_action( 'give_complete_purchase', 'bpg_add_activity_item' );

/**
 * Add a sub nav item to the member profile area.
 *
 * @author bowefrankema
 * @since 1.0.0
 */
function bpg_setup_donations_nav() {

	if ( ! bp_is_active( 'xprofile' ) )
		return;

    $bp = buddypress();

    $profile_link = bp_loggedin_user_domain() . $bp->profile->slug . '/';

    $args = array(
        'name' => __( 'My Donations', 'buddypress-give' ),
        'slug' => 'my-donations',
        'parent_url' => $profile_link,
        'parent_slug' => $bp->profile->slug,
        'screen_function' => 'bpg_give_screen_donations',
        'user_has_access' => ( bp_is_my_profile() || is_super_admin() ),
        'position' => 40
    );
    bp_core_new_subnav_item( $args );
}
add_action( 'bp_setup_nav', 'bpg_setup_donations_nav' );

/**
 * Sets up and displays the screen output for the sub nav item.
 *
 * @author bowefrankema
 * @since 1.0.0
 */
function bpg_give_screen_donations() {

    add_action( 'bp_template_title', 'bpg_page_title' );
    add_action( 'bp_template_content', 'bpg_page_content' );
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

/**
 * Output a page title.
 *
 * @author bowefrankema
 * @since 1.0.0
 */
function bpg_page_title() {
    echo __( 'Your Donations', 'buddypress-give' );
}

/**
 * Output page content.
 *
 * @author bowefrankema
 * @since 1.0.0
 */
function bpg_page_content() {
    ?>
    <div id="give-my-donations">        
    <?php echo do_shortcode( '[donation_history]' ); ?>
    </div>

    <div>
    <?php
    _e( 'Make a donation', 'buddypress-give' );

	// @todo Make the form ID dynamic.
    // echo do_shortcode( '[give_form id="688"]' );
    ?>
    </div>
    <?php
}

/**
 * Redirect logged in users from the Give page to the BuddyPress page.
 *
 * @author bowefrankema
 * @since 1.0.0
 */
function bpg_redirect() {

	if ( ! bp_is_active( 'xprofile' ) )
		return;

	if ( is_user_logged_in() && is_page( 'donations' ) ) {

		$bp = buddypress();
		wp_redirect( bp_loggedin_user_domain() . $bp->profile->slug . '/my-donations/', 301 );
		exit(); 
	}
}
// add_action( 'template_redirect', 'bpg_redirect' );