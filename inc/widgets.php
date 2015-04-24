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

		// Check if widget_id is set.
		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		// Check if before_widget is set.
		if ( ! isset( $args['before_widget'] ) )
			$args['before_widget'] = '';

		// Check if after_widget is set.
		if ( ! isset( $args['after_widget'] ) )
			$args['after_widget'] = '';

		// Check if before_title is set.
		if ( ! isset( $args['before_title'] ) )
			$args['before_title'] = '';

		// Check if after_title is set.
		if ( ! isset( $args['after_title'] ) )
			$args['after_title'] = '';

		// Check if title is set.
		if ( ! isset( $instance['title'] ) )
			$instance['title'] = __( 'Top donors', 'buddypress-give' );

		// Check if number is set.
		if ( ! isset( $instance['number'] ) )
			$instance['number'] = 5;

		// Check if show_total is set.
		if ( ! isset( $instance['show_total'] ) )
			$instance['show_total'] = false;

		echo $args['before_widget'];

		echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];

		$customers = new Give_DB_Customers();

		// Get X donors in descending order of total donation amount.
		$query_args = array(
			'number' => absint( $instance['number'] ),
			'orderby' => 'purchase_value',
			'order' => 'DESC'
		);

		$donors = $customers->get_customers( $query_args );

		if ( $donors ) { ?>
			<ul class="bp-give-leaderboard item-list">
			<?php foreach ( $donors as $donor ) { ?>

				<?php $user = get_userdata( $donor->user_id ); ?>
				<li class="vcard bp-give-leaderboard-item">
					<div class="item-avatar">
						<?php echo bp_core_fetch_avatar( array(
							'item_id' => $user->ID,
							'width' => 26,
							'height' => 26,
							'alt' => $user->display_name
						) ); ?>
					</div>
					<div class="item-title">
						<a href="<?php echo esc_url( bp_core_get_user_domain( $user->ID ) ); ?>" title="<?php echo esc_attr( $user->display_name ); ?>"><?php echo esc_html( $user->display_name ); ?></a>
					</div>
					<div class="item-meta">
					<?php if ( (bool) $instance['show_total'] ) { ?>
						<span class="purchase-value"><?php echo esc_html( give_currency_symbol() ); ?><?php echo esc_html( $donor->purchase_value ); ?></span>
					<?php } ?>
					</div>
				</li>
			<?php } ?>
			</ul>
		<?php } else { ?>
			<?php $notice = __( 'No donations have been made.', 'buddypress-give' ); ?>
			<p class="bp-give-notice"><?php echo esc_html( $notice ); ?></p>
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

		if ( ! isset( $new_instance['title'] ) )
			$instance['title'] = '';
		else
			$instance['title'] = sanitize_title( $new_instance['title'] );

		if ( ! isset( $new_instance['number'] ) )
			$instance['number'] = 5;
		else
			$instance['number'] = absint( $new_instance['number'] );

		if ( ! isset( $new_instance['show_total'] ) )
			$instance['show_total'] = false;
		else
			$instance['show_total'] = (bool) $new_instance['show_total'];

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

		if ( ! isset( $instance['title'] ) )
			$instance['title'] = '';
		else
			$instance['title'] = sanitize_title( $instance['title'] );

		if ( ! isset( $instance['number'] ) )
			$instance['number'] = 5;
		else
			$instance['number'] = absint( $instance['number'] );

		if ( ! isset( $instance['show_total'] ) )
			$instance['show_total'] = false;
		else
			$instance['show_total'] = (bool) $instance['show_total'];

		$label_title = __( 'Title', 'buddypress-give' );
		$label_number = __( 'Number of donors to show', 'buddypress-give' );
		$label_show = __( 'Display total donated?', 'buddypress-give' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html( $label_title ); ?></label>
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" class="widefat" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php echo esc_html( $label_number ); ?></label>
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" value="<?php echo esc_attr( $instance['number'] ); ?>" size="3" />
		</p>

		<p>
			<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_total' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'show_total' ) ); ?>" class="checkbox" <?php checked( $instance['show_total'] ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_total' ) ); ?>"><?php echo esc_html( $label_show ); ?></label>
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