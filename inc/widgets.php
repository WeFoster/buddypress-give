<?php
/**
 * Widgets
 *
 * @package BuddyPress Give
 * @subpackage Widgets
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * The widget class definition.
 *
 * @since 1.0.0
 */
class Donation_Leaderboard_Widget extends WP_Widget {

	/**
	 * Constructor method.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		$widget_options = array( 
			'description' => __( 'A widget to show the top donors on the site.', 'buddypress-give' ) 
		);

		parent::__construct( 
			'donation-leaderboard', 
			__( 'Donation Leaderboard' ), 
			$widget_options 
		);
	}

	/**
	 * Output the widget content.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args The display arguments.
	 * @param array $instance The settings for the instance of the widget.
	 */
	public function widget( $args, $instance ) {

		$args['widget_id'] = ! empty( $args['widget_id'] ) ? $args['widget_id'] : $this->id;
		$args['before_widget'] = ! empty( $args['before_widget'] ) ? $args['before_widget'] : '';
		$args['after_widget'] = ! empty( $args['after_widget'] ) ? $args['after_widget'] : '';
		$args['before_title'] = ! empty( $args['before_title'] ) ? $args['before_title'] : '';
		$args['after_title'] = ! empty( $args['after_title'] ) ? $args['after_title'] : '';

		$instance['title'] = ! empty( $instance['title'] ) ? sanitize_title( $instance['title'] ) : __( 'Top donors', 'buddypress-give' );
		$instance['number'] = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$instance['show_total'] = isset( $instance['show_total'] ) ? (bool) $instance['show_total'] : false;

		echo $args['before_widget'];

		echo $args['before_title'] . $instance['title'] . $args['after_title'];

		$customers = new Give_DB_Customers();

		// Get X donors in descending order of total donation amount.
		$query_args = array(
			'number' => $instance['number'],
			'orderby' => 'purchase_value',
			'order' => 'DESC'
		);

		$donors = $customers->get_customers( $query_args );

		if ( $donors ) { ?>
			<ul class="bp-give-leaderboard item-list">
			<?php foreach ( $donors as $donor ) { ?>

				<?php $user = get_userdata( $donor->user_id ); ?>
				<li class"vcard bp-give-leaderboard-item">
					<div class="item-avatar">
						<?php
						$avatar_args = array(
							'item_id' => $user->ID,
							'width' => 26,
							'height' => 26,
							'alt' => $user->display_name
						);
						echo bp_core_fetch_avatar( $avatar_args );
						?>
					</div>
					<div class="item-title">
						<a href="<?php echo bp_core_get_user_domain( $user->ID ); ?>" title="<?php echo $user->display_name; ?>"><?php echo $user->display_name; ?>
						</a>
					</div>
					<div class="item-meta">
						<?php if ( $instance['show_total'] ) {
						echo '<span class="bp-give-purchase-value">' . $donor->purchase_value . ' ' . give_get_currency() . '</span>';
						} ?>
					</div>
				</li>
			<?php } ?>
			</ul>
		<?php } else { ?>
			<p class="bp-give-notice"><?php _e( 'No donations have been made.', 'buddypress-give' ); ?></p>
		<?php }

		echo $args['after_widget'];
	}

	/**
	 * Update a particular instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $new_instance The new settings for this instance as input by the user.
	 * @param array $old_instance The old settings for this instance.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance['title'] = ! empty( $new_instance['title'] ) ? sanitize_title( $new_instance['title'] ) : '';
		$instance['number'] = ! empty( $new_instance['number'] ) ? absint( $new_instance['number'] ) : 5;
		$instance['show_total'] = ! empty( $new_instance['show_total'] ) ? (bool) $new_instance['show_total'] : false;

		return $instance;
	}

	/**
	 * Output the settings form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {

		$instance['title'] = ! empty( $instance['title'] ) ? sanitize_title( $instance['title'] ) : '';
		$instance['number'] = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$instance['show_total'] = ! empty( $instance['show_total'] ) ? (bool) $instance['show_total'] : false;
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'buddypress-give' ); ?></label>
		<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat" value="<?php echo $instance['title']; ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of donors to show', 'buddypress-give' ); ?></label>
		<input type="text" name="<?php echo $this->get_field_name( 'number' ); ?>" id="<?php echo $this->get_field_id( 'number' ); ?>" value="<?php echo $instance['number']; ?>" size="3" />
		</p>

		<p>
		<input type="checkbox" name="<?php echo $this->get_field_name( 'show_total' ); ?>" id="<?php echo $this->get_field_id( 'show_total' ); ?>" class="checkbox" <?php checked( $instance['show_total'] ); ?> />
		<label for="<?php echo $this->get_field_id( 'show_total' ); ?>"><?php _e( 'Display total donated?', 'buddypress-give' ); ?></label>
		</p>
		<?php
	}
}

/**
 * Register the widget.
 *
 * @since 1.0.0
 */
function bpg_register_leaderboard_widget() {
	register_widget( 'Donation_Leaderboard_Widget' );
}
add_action( 'widgets_init', 'bpg_register_leaderboard_widget' );