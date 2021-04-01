<?php

if ( ! defined( "ABSPATH" ) ) {
	exit; // Exit if accessed directly.
}

do_action( "b4b_woocommerce_before_customer_register_form" ); ?>

    <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( "woocommerce_register_form_tag" ); ?> >

		<?php do_action( "b4b_woocommerce_register_form_start" ); ?>

        <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
            <label for="account_first_name"><?php esc_html_e( 'First name', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo ( ! empty( $_POST["account_first_name"] ) ) ? esc_attr( wp_unslash( $_POST["account_first_name"] ) ) : ""; ?>"/>
        </p>
        <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
            <label for="account_last_name"><?php esc_html_e( 'Last name', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo ( ! empty( $_POST["account_last_name"] ) ) ? esc_attr( wp_unslash( $_POST["account_last_name"] ) ) : ""; ?>"/>
        </p>
        <div class="clear"></div>

		<?php if ( "no" === get_option( "woocommerce_registration_generate_username" ) ) : ?>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="reg_username"><?php esc_html_e( "Username", "woocommerce" ); ?>&nbsp;<span class="required">*</span></label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST["username"] ) ) ? esc_attr( wp_unslash( $_POST["username"] ) ) : ""; ?>"/><?php // @codingStandardsIgnoreLine ?>
            </p>

		<?php endif; ?>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="reg_email"><?php esc_html_e( "Email address", "woocommerce" ); ?>&nbsp;<span class="required">*</span></label>
            <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST["email"] ) ) ? esc_attr( wp_unslash( $_POST["email"] ) ) : ""; ?>"/><?php // @codingStandardsIgnoreLine ?>
        </p>

		<?php if ( "no" === get_option( "woocommerce_registration_generate_password" ) ) : ?>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="reg_password"><?php esc_html_e( "Password", "woocommerce" ); ?>&nbsp;<span class="required">*</span></label>
                <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password"/>
            </p>

		<?php else : ?>

            <p><?php esc_html_e( "A password will be sent to your email address.", "woocommerce" ); ?></p>

		<?php endif; ?>

		<?php do_action( "b4b_woocommerce_register_form" ); ?>

        <p class="woocommerce-FormRow form-row">
			<?php wp_nonce_field( "woocommerce-register", "woocommerce-register-nonce" ); ?>
            <button type="submit" class="woocommerce-Button button" name="register" value="<?php esc_attr_e( "Register", "woocommerce" ); ?>"><?php esc_html_e( "Register", "woocommerce" ); ?></button>
        </p>

		<?php do_action( "b4b_woocommerce_register_form_end" ); ?>

    </form>

<?php do_action( "b4b_woocommerce_after_customer_register_form" ); ?>