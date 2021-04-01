<?php
/**
 * An email sent to the customers when they didn't fill in all the data for scramble event.
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
<p><?php esc_html_e( 'We need the information below to complete your scramble team. Please log into your account and update it as soon as you can.', 'b4b-theme-support' ); ?></p>

<p><strong><?php esc_html_e( 'Missing Information', 'b4b-theme-support' ); ?></strong></p>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px;" border="1">
	<thead>
		<tr>
			<th class="td" scope="col"><?php esc_html_e( 'Name', 'b4b-theme-support' ); ?></th>
			<th class="td" scope="col"><?php esc_html_e( 'Email', 'b4b-theme-support' ); ?></th>
			<th class="td" scope="col"><?php esc_html_e( 'Phone', 'b4b-theme-support' ); ?></th>
			<th class="td" scope="col"><?php esc_html_e( 'Shirt size', 'b4b-theme-support' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $email->args['accounts'] as $item ) : ?>
			<tr>
				<?php if ( empty( $item['name'] ) ) : ?>
					<td class="td" style="background:red; color: #ffffff;"><?php esc_html_e( 'missing', 'b4b-theme-support' ); ?></td>
				<?php else: ?>
					<td class="td"><?php echo esc_html( $item['name'] ); ?></td>
				<?php endif; ?>

				<?php if ( empty( $item['email'] ) ) : ?>
					<td class="td" style="background:red; color: #ffffff;"><?php esc_html_e( 'missing', 'b4b-theme-support' ); ?></td>
				<?php else: ?>
					<td class="td"><?php echo esc_html( $item['email'] ); ?></td>
				<?php endif; ?>

				<?php if ( empty( $item['phone'] ) ) : ?>
					<td class="td" style="background:red; color: #ffffff;"><?php esc_html_e( 'missing', 'b4b-theme-support' ); ?></td>
				<?php else: ?>
					<td class="td"><?php echo esc_html( $item['phone'] ); ?></td>
				<?php endif; ?>

				<?php if ( empty( $item['shirt_size'] ) ) : ?>
					<td class="td" style="background:red; color: #ffffff;"><?php esc_html_e( 'missing', 'b4b-theme-support' ); ?></td>
				<?php else: ?>
					<td class="td"><?php echo esc_html( $item['shirt_size'] ); ?></td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<br>
<p style="text-align:center;">
	<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>" style="padding:15px 50px; background: #00cc66; border-radius:5px; color:#ffffff; font-size:16px; text-decoration:none;"><?php esc_html_e( 'UPDATE THIS INFO - LOG INTO B4B', 'b4b-theme-support' ); ?></a>
</p>
<p style="text-align:center;">
	<br>
	<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" style="color:#999999;"><?php esc_html_e( 'forgot your password?', 'b4b-theme-support' ); ?></a>
</p>

<br>
<p>
	<?php esc_html_e( 'See you at the scramble,', 'b4b-theme-support' ); ?>
	<br><?php esc_html_e( 'Birdies 4 Brains Support Squad', 'b4b-theme-support' ); ?>
</p>
<?php

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
