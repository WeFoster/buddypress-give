<?php
/**
 * Class definitions
 *
 * @package BuddyPress Give
 * @subpackage Classes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * The donation badge class definition.
 *
 * @since 1.0.0
 */
class Donation_Badge {

	/**
	 * The ID of the badge owner.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var int
	 */
	public $user_id;

	/**
	 * The total amount donated.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var float
	 */
	public $purchase_value;

	/**
	 * Set up values.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $user_id The ID of the badge owner.
	 */
	function __construct( $user_id ) {

		$this->user_id = (int) $user_id;

	}

	/**
	 * Get a badge for a given user.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The HTML output of the donation badge.
	 */
	function get() {

		$badge = '';

		$badges = get_option( 'bpg-badges' );

		if ( empty( $badges ) ) {
			return $badge;
		}

		// Count the number of badges available.
		$count = count( $badges['_give_badges'] );

		// Get the total donated so far.
		$this->purchase_value();

		for ( $i = 0; $i < $count; $i++ ) {

			// Check if this is the last badge.
			if ( $i == ( $count - 1 ) ) {

				if ( ( $this->purchase_value > floatval( $badges['_give_badges'][$i]['_give_amount'] ) ) ) {
					$badge = '<img src="' . $badges['_give_badges'][$i]['_give_image'] . '" alt="' . $badges['_give_badges'][$i]['_give_text'] . '" />';
					break;
				}
			} else {

				if ( ( $this->purchase_value > floatval( $badges['_give_badges'][$i]['_give_amount'] ) ) && ( $this->purchase_value < floatval( $badges['_give_badges'][$i + 1]['_give_amount'] ) ) ) {
					$badge = '<img src="' . $badges['_give_badges'][$i]['_give_image'] . '" alt="' . $badges['_give_badges'][$i]['_give_text'] . '" />';
					break;
				}
			}

		}
		return $badge;
	}

	/**
	 * Get the total donated amount for a given user.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function purchase_value() {

		// Check if the amount is cached.
		$purchase_value = wp_cache_get( $this->user_id, 'purchase_value' );

		if ( false === $purchase_value ) {

			global $wpdb;

			$table_name = $wpdb->prefix . 'give_customers';

			$purchase_value = $wpdb->get_var( $wpdb->prepare( "SELECT purchase_value FROM {$table_name} WHERE user_id = %d", $this->user_id ) );

			// Cache the query result.
			wp_cache_set( $this->user_id, $purchase_value, 'purchase_value' );
		}

		$this->purchase_value = floatval( $purchase_value );
	}
}