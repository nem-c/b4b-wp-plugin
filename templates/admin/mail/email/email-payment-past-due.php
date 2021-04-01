<?php
/**
 * An email sent to the customers when they have past due payments.
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
<p><?php esc_html_e( "We have you registered for the event(s) below, but we're still missing payment from you. Please click the links below to complete payment as soon as you can.", 'b4b-theme-support' ); ?></p>

<p><strong><?php esc_html_e( 'Missing Payments', 'b4b-theme-support' ); ?></strong></p>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px;" border="1">
	<thead>
		<tr>
			<th class="td" scope="col"><?php esc_html_e( 'Event', 'b4b-theme-support' ); ?></th>
			<th class="td" scope="col"><?php esc_html_e( 'Amount', 'b4b-theme-support' ); ?></th>
			<th class="td" scope="col"><?php esc_html_e( 'Paid', 'b4b-theme-support' ); ?></th>
			<th class="td" scope="col"><?php esc_html_e( 'Make Payment', 'b4b-theme-support' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $email->args['events'] as $event ) : ?>
			<tr>
				<td class="td"><?php echo esc_html( $event['name'] ); ?></td>
				<td class="td"><?php echo esc_html( $event['amount'] ); ?></td>
				<td class="td"><?php echo esc_html( $event['paid'] ); ?></td>
				<td class="td"><a href="<?php echo esc_attr( $event['make_payment'] ); ?>" style="color: #0000ff;"><?php esc_html_e( 'Make Payment', 'b4b-theme-support' ); ?></a></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<br>
<p style="text-align:center;">
	<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" style="padding:15px 50px; background: #00cc66; border-radius:5px; color:#ffffff; font-size:16px; text-decoration:none;"><?php esc_html_e( 'MAKE PAYMENT - LOG INTO B4B', 'b4b-theme-support' ); ?></a>
</p>
<p style="text-align:center;">
	<br>
	<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" style="color:#999999;"><?php esc_html_e( 'forgot your password?', 'b4b-theme-support' ); ?></a>
</p>

<br>
<p>
	<?php esc_html_e( 'Thank you,', 'b4b-theme-support' ); ?>
	<br><?php esc_html_e( 'Birdies 4 Brains Support Squad', 'b4b-theme-support' ); ?>
</p>
<?php

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
