<?php
/**
 * An email sent to the customers when they didn't will in all the data for 100 holes event.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s: Customer first name */ ?>
<p><?php printf( esc_html__( 'Hey %s,', 'b4b-theme-support' ), esc_html( $email->args['first_name'] ) ); ?></p>
<p><?php echo esc_html( sprintf( __( "In %d you've raised %s of your %s fundraising goal. All funds raised for Birdies 4 Brains goes directly to local people whom need it, so keep up the hard work :)", 'b4b-theme-support' ), $email->args['year'], $email->args['money_raised'], $email->args['money_goal'] ) ); ?></p>

<?php if ( !empty( $email->args['donation_link'] ) ) : ?>
	<br>
	<p><strong><?php esc_html_e( 'Share your birdies donation link.', 'b4b-theme-support' ); ?></strong></p>
	<p><a href="<?php echo esc_url( $email->args['donation_link'] ); ?>"><?php echo esc_html( $email->args['donation_link'] ); ?></a></p>
<?php endif; ?>

<br>
<p style="text-align:center;"><strong><?php esc_html_e( 'Want to see who donated?', 'b4b-theme-support' ); ?></strong></p>
<p style="text-align:center;">
	<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" style="padding:15px 50px; background: #00cc66; border-radius:5px; color:#ffffff; font-size:16px; text-decoration:none;"><?php esc_html_e( 'LOG INTO B4B', 'b4b-theme-support' ); ?></a>
</p>
<p style="text-align:center;">
	<br>
	<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" style="color:#999999;"><?php esc_html_e( 'forgot your password?', 'b4b-theme-support' ); ?></a>
</p>

<br>
<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px; border:0;">
	<thead>
		<tr>
			<th class="td" scope="col" colspan="2"><strong><?php echo esc_html( sprintf( __( 'Top 10 Fundraisers in %d', 'b4b-theme-support' ), $email->args['year'] ) ); ?></strong></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $email->args['fundraisers'] as $counter => $item ) : 
			if($item['name'] == '_Birdies 4 Brains Donation'){
			} else { ?>
				<tr>
					<td class="td"><?php echo $counter+1, '. ', esc_html( $item['name'] ); ?></td>
					<td class="td"><?php echo esc_html( $item['amount'] ); ?></td>
				</tr>
			<?php }
		endforeach; ?>
	</tbody>
</table>

<br>
<p>
	<?php esc_html_e( 'See you at the 100 hole event,', 'b4b-theme-support' ); ?>
	<br><?php esc_html_e( 'Birdies 4 Brains Support Squad', 'b4b-theme-support' ); ?>
</p>
<?php

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
