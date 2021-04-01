<style>
.registration-popup-backdrop {
	display: none;
	position: fixed;
	content: "";
	top: 0;
	left: 0;
	bottom: 0;
	right: 0;
	background: rgba(0, 0, 0, .5);
	z-index: 1000;
}
.registration-popup-backdrop.show {
	display: block;
}
.registration-popup {
	position: fixed;
	padding: 15px;
	top: 50%;
	left: 50%;
	margin-top: -100px;
	margin-left: -160px;
	max-width: 100%;
	width: 400px;
	background: #fff;
	z-index: 1001;
	text-align: center;
}
.registration-popup-body {
	padding-bottom: 2em;
}
.registration-popup-footer .button:first-child {
	margin-right: 10px;
}

.registration-popup-trigger {
	display: none;
}
</style>


<div id="registration-popup" class="registration-popup-backdrop">
	<div class="registration-popup">
		<div class="registration-popup-body">
			It appears you are already registered on this website!
		</div>
		<div class="registration-popup-footer">
			<a href="<?php echo wc_get_endpoint_url( 'login', '', wc_get_page_permalink( 'myaccount' ) ) ?>" class="woocommerce-Button button">Login</a>
			<a href="<?php echo wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) ) ?>" class="woocommerce-Button button">Reset password</a>
		</div>
	</div>
</div>


<script>
document.addEventListener( 'DOMContentLoaded', function () {
	var $ = jQuery,
		$form = $( '.woocommerce-form-register' );

	$form.on( 'submit', function ( event ) {
		event.preventDefault();

		$.ajax( {
			url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'b4b_registration_check',
				email: $form.find( '#reg_email' ).val(),
				phone: $form.find( '#billing_phone' ).val()
			},
			success: function ( response ) {
				if ( response.exists ) {
					$( '#registration-popup' )
						.addClass( 'show' )
						.find( '.registration-popup-body' )
						.text( response.message );
				} else {
					// $form[0].submit();

					// We need "name=register" in request
					$( 'form' ).off( 'submit' );
					$form.find( 'button[name=register]' ).click();
				}
			},
			error: function ( XMLHttpRequest, textStatus, exception ) {
				console.log( 'Ajax failure: ' + XMLHttpRequest.statusText );
			},
			async: true
		} );

	} );

} );
</script>
