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

	$label = __( 'Custom donation message', 'buddypress-give' );
	$tip = __( 'Please enter a custom donation message', 'buddypress-give' );

	?>
	<div class="bp-give-message-wrap">
		<label class="give-label" for="give-message"><?php echo esc_html( $label ); ?></label>
		<span class="give-tooltip icon icon-question" data-tooltip="<?php echo esc_attr( $tip ); ?>"></span>

		<textarea class="bp-give-textarea" name="give_message" id="give-message"></textarea>
	</div>

	<!-- Pass user email in a hidden field -->
	<input type="hidden" name="give_email" value="<?php echo sanitize_email( $user->user_email ); ?>">
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
 * Add donation message to payment meta.
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
	$label = __( 'Message', 'buddypress-give' );
	?>
	<div class="bp-give-message-data">
		<label><?php echo esc_html( $label ); ?></label>
		<p><?php echo esc_html( $payment_meta['message'] ); ?></p>
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

		$label = __( 'Message', 'buddypress-give' );
		?>
		<tr>
			<td><strong><?php echo esc_html( $label ); ?>:</strong></td>
			<td><?php echo esc_html( $payment_meta['message'] ); ?></td>
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

	if ( ! isset( $option_data['bpg-show-amount'] ) ) {
		$option_data['bpg-show-amount'] = '';
	}

	if ( ! isset( $option_data['bpg-default-message'] ) ) {
		$option_data['bpg-default-message'] = '';
	}

	// Get donor info.
	$user = get_userdata( $payment_meta['user_info']['id'] );

	$primary_link = bp_core_get_userlink( $user->ID, false, true );

	$donor = '<a href="' . $primary_link . '" title="' . $user->display_name . '">' . $user->display_name . '</a>';

	if ( $option_data['bpg-show-amount'] == "1" ) {
		$action = sprintf( __( '%s just donated %s%1.2f to %s', 'buddypress-give' ), $donor, give_currency_symbol(), (float) $payment_total, get_the_title( $payment_meta['form_id'] ) );
	} else {
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
	$title = __( 'Your Donations', 'buddypress-give' );
	echo esc_html( $title );
}

/**
 * Output page content.
 *
 * @author bowefrankema
 * @since 1.0.0
 */
function bpg_page_content() {

	$give_settings = get_blog_option( get_current_blog_id(), 'give_settings' );

	$option_data = get_blog_option( get_current_blog_id(), 'bpg-options' );

	echo get_post_field( 'post_content', $give_settings['history_page'] );

	if ( $option_data['bpg-form-id'] ) {

		$text = __( 'Make a donation', 'buddypress-give' );
		echo esc_html( $text );

		echo do_shortcode( '[give_form id="' . $option_data['bpg-form-id'] . '"]' );
	}
}

/**
 * Redirect logged in users from the Give page to the BuddyPress page.
 *
 * @author bowefrankema
 * @since 1.0.0
 */
function bpg_redirect() {

	// Bail if the xProfile component isn't active.
	if ( ! bp_is_active( 'xprofile' ) )
		return;

	// Bail if the user isn't logged in.
	if ( ! is_user_logged_in() )
		return;

	if ( is_page( 'donation-history' ) ) {

		bp_core_redirect( bp_loggedin_user_domain() . buddypress()->profile->slug . '/my-donations/' );
	}
}
add_action( 'wp', 'bpg_redirect' );

/**
 * Output a donation badge next to each member's full avatar.
 *
 * @since 1.0.0
 *
 * @param string $avatar The member's avatar HTML.
 * @return string
 */
function bpg_add_donation_badge( $avatar ) {

	$option_data = get_blog_option( get_current_blog_id(), 'bpg-options' );

	if ( empty( $option_data['bpg-avatar-badge'] ) )
		return $avatar;

	if ( $option_data['bpg-avatar-badge-thumb'] )
		return $avatar;

	$obj = new Donation_Badge( bp_displayed_user_id() );

	$badge = $obj->get_highest();

	return $avatar . $badge;
}
add_filter( 'bp_get_displayed_user_avatar', 'bpg_add_donation_badge' );

/**
 * Output a donation badge next to each member's thumb avatar.
 *
 * @since 1.0.0
 */
function bpg_add_donation_badge_avatar_thumb( $avatar, $params, $item_id, $avatar_dir, $html_css_id, $html_width, $html_height, $avatar_folder_url, $avatar_folder_dir ) {

	$option_data = get_blog_option( get_current_blog_id(), 'bpg-options' );

	if ( empty( $option_data['bpg-avatar-badge-thumb'] ) )
		return $avatar;

	$obj = new Donation_Badge( $item_id );

	$badge = $obj->get_highest();

	return $avatar . $badge;
}
add_filter( 'bp_core_fetch_avatar', 'bpg_add_donation_badge_avatar_thumb', 10, 9 );

/**
 * Output all earned donation badges in the member's profile header area.
 *
 * @since 1.0.0
 */
function bpg_add_donation_badges() {

	$option_data = get_blog_option( get_current_blog_id(), 'bpg-options' );

	if ( empty( $option_data['bpg-profile-badges'] ) )
		return;

	$obj = new Donation_Badge( bp_displayed_user_id() );

	$badges = $obj->get_all();

	echo $badges;
}
add_action( 'bp_before_member_header_meta', 'bpg_add_donation_badges' );