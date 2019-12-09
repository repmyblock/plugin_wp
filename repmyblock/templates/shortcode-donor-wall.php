<?php
/**
 * This template is used to display the donation grid with [walkthecounty_donor_wall]
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var $donor WalkTheCounty_Donor */
$donation = $args[0];

$walkthecounty_settings = $args[1]; // WalkTheCounty settings.
$atts          = $args[2]; // Shortcode attributes.
?>

<div class="walkthecounty-grid__item">
	<div class="walkthecounty-donor walkthecounty-card">
		<div class="walkthecounty-donor__header">
			<?php
			if( true === $atts['show_avatar'] ) {

				// Get anonymous donor image.
				$anonymous_donor_img = sprintf(
					'<img src="%1$s" alt="%2$s">',
					esc_url( WALKTHECOUNTY_PLUGIN_URL . 'assets/dist/images/anonymous-user.svg' ),
					esc_attr__( 'Anonymous User', 'walkthecounty' )
				);

				// Get donor avatar image based on donation parameter.
				$donor_avatar = ! empty( $donation['_walkthecounty_anonymous_donation'] ) ? $anonymous_donor_img : $donation['name_initial'];

				// Validate donor gravatar.
				$validate_gravatar = ! empty( $donation['_walkthecounty_anonymous_donation'] ) ? 0 : walkthecounty_validate_gravatar( $donation['_walkthecounty_payment_donor_email'] );

				// Maybe display the Avatar.
				echo sprintf(
					'<div class="walkthecounty-donor__image" data-donor_email="%1$s" data-has-valid-gravatar="%2$s">%3$s</div>',
					md5( strtolower( trim( $donation['_walkthecounty_payment_donor_email'] ) ) ),
					absint( $validate_gravatar ),
					$donor_avatar
				);
			}
			?>

			<div class="walkthecounty-donor__details">
				<?php if ( true === $atts['show_name'] ) : ?>
					<h3 class="walkthecounty-donor__name">
						<?php
						// Get donor name based on donation parameter.
						$donor_name = ! empty( $donation['_walkthecounty_anonymous_donation'] )
							? __( 'Anonymous', 'walkthecounty' )
							: trim( $donation['_walkthecounty_donor_billing_first_name'] . ' ' . $donation['_walkthecounty_donor_billing_last_name'] );
						?>
						<?php esc_html_e( $donor_name ); ?>
					</h3>
				<?php endif; ?>

				<?php if ( true === $atts['show_total'] ) : ?>
					<span class="walkthecounty-donor__total">
						<?php echo esc_html( walkthecounty_donation_amount( $donation['donation_id'], true ) ); ?>
					</span>
				<?php endif; ?>

				<?php if ( true === $atts['show_time'] ) : ?>
					<span class="walkthecounty-donor__timestamp">
						<?php echo esc_html( walkthecounty_get_formatted_date( $donation[ 'donation_date' ], walkthecounty_date_format(), 'Y-m-d H:i:s' ) ); ?>
					</span>
				<?php endif; ?>
			</div>
		</div>

		<?php
		if (
			true === $atts['show_comments']
			&& absint( $atts['comment_length'] )
			&& ! empty( $donation['donor_comment'] )
			&& ! $donation['_walkthecounty_anonymous_donation']
		) :
			?>
			<div class="walkthecounty-donor__content">
				<?php
				$comment     = trim( $donation['donor_comment'] );
				$total_chars = strlen( $comment );
				$max_chars   = $atts['comment_length'];

				// A truncated excerpt is displayed if the comment is too long.
				if ( $max_chars < $total_chars ) {
					$excerpt    = '';
					$offset     = -( $total_chars - $max_chars );
					$last_space = strrpos( $comment, ' ', $offset );

					if ( $last_space ) {
						// Truncate excerpt at last space before limit.
						$excerpt = substr( $comment, 0, $last_space );
					} else {
						// There are no spaces, so truncate excerpt at limit.
						$excerpt = substr( $comment, 0, $max_chars );
					}

					$excerpt = trim( $excerpt, '.!,:;' );

					echo sprintf(
						'<p class="walkthecounty-donor__excerpt">%s&hellip;<span> <a class="walkthecounty-donor__read-more">%s</a></span></p>',
						nl2br( esc_html( $excerpt ) ),
						esc_html( $atts['readmore_text'] )
					);
				}

				echo sprintf(
					'<p class="walkthecounty-donor__comment">%s</p>',
					nl2br( esc_html( $comment ) )
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</div>
