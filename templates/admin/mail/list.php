
<style>
/* Table */
.column-type {
	width: 200px;
}

.column-last_sent_at,
.column-next_send_at {
	width: 100px;
}

.column-frequency {
	width: 150px;
}

.column-actions {
	width: 80px;
}

/* Popup */
.b4b-mail-popup-backdrop {
    display: none;
    position: fixed;
    content: "";
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background: rgba(0, 0, 0, .5);
    z-index: 10000;
}
.b4b-mail-popup-backdrop.show {
    display: block;
}
.b4b-mail-popup {
    position: fixed;
    padding: 15px;
    top: 50%;
    left: 50%;
    margin-top: -200px;
    margin-left: -230px;
    max-width: 100%;
    width: 460px;
    background: #fff;
    z-index: 10001;
}
.b4b-mail-popup-body {
    padding-bottom: 2em;
}
.b4b-mail-popup-footer {
	text-align: center;
}
.b4b-mail-popup-footer .button {
    margin-right: 10px;
}
.b4b-mail-popup-footer .button:last-child {
    margin-right: 0;
}

.b4b-field {
	margin: 1em 0;
}
.b4b-field label {
	display: block;
}
.b4b-field input,
.b4b-field select {
	width: 100%;
	max-width: 100%;
}
.b4b-field-wrapper-frequency_value label {
	display: inline-block;
}
.b4b-field-wrapper-frequency_value input {
	display: inline-block;
	margin: 0 3px;
	width: 60px;
}
.b4b-field-wrapper-frequency_value select {
	display: inline-block;
	margin: 0 3px;
	width: 100px;
}

#b4b-mail-popup-send-confirmation .b4b-mail-popup-body {
	text-align: center;
}
</style>

<div class="wrap">
	<h2><?php _e( 'Auto Emails', 'b4b-theme-support' ); ?></h2>
	<hr class="wp-header-end" />

	<form method="post">
		<?php
		/** @var $wp_list_table B4b_Mails_List_Table */
		$wp_list_table->prepare_items();
		$wp_list_table->display();
		?>
	</form>
</div>


<div id="b4b-mail-popup-send-confirmation" class="b4b-mail-popup-backdrop">
    <div class="b4b-mail-popup">
		<div class="b4b-mail-popup-body">
			<h3><?php _e( 'Are you sure you wish to manually send these emails?', 'b4b-theme-support' ); ?></h3>
		</div>
		<div class="b4b-mail-popup-footer">
			<input type="button" class="button button-large button-secondary" id="b4b-mail-popup-send-confirmation-cancel" value="<?php _e( 'Cancel', 'b4b-theme-support' ); ?>">
			<a href="#" class="button button-large button-primary" id="b4b-mail-popup-send-confirmation-confirm"><?php _e( 'Send', 'b4b-theme-support' ); ?></a>
		</div>
    </div>
</div><!-- .b4b-mail-popup-send-confirmation -->


<div id="b4b-mail-popup" class="b4b-mail-popup-backdrop">
    <div class="b4b-mail-popup">
		<form method="post" id="b4b-mail-form-edit">
			<div class="b4b-mail-popup-body">
				<h3 id="b4b-popup-title"></h3>
				<div class="b4b-field">
					<label for="b4b-field-email_subject"><?php _e( 'Email subject', 'b4b-theme-support' ); ?></label>
					<input type="text" name="email_subject" id="b4b-field-email_subject" required="required" minlength="1" maxlength="100" />
				</div>
				<div class="b4b-field">
					<label for="b4b-field-frequency_type"><?php _e( 'Frequency', 'b4b-theme-support' ); ?></label>
					<select name="frequency_type" id="b4b-field-frequency_type">
						<?php foreach ( $wp_list_table->get_frequency_types() as $frequency_type_key => $frequency_type_label ) : ?>
							<option value="<?php echo esc_html( $frequency_type_key ); ?>"><?php echo esc_html( $frequency_type_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="b4b-field b4b-field-wrapper-frequency_value b4b-field-wrapper-frequency_value-manual">
					<input type="hidden" name="frequency_value" value="0" disabled="disabled" />
				</div>
				<div class="b4b-field b4b-field-wrapper-frequency_value b4b-field-wrapper-frequency_value-monthly">
					<?php _e( 'Send on day', 'b4b-theme-support' ); ?>
					<input type="number" name="frequency_value" value="1" disabled="disabled" required="required" min="1" max="28" />
					<?php printf( __( 'at %s', 'b4b-theme-support' ), $wp_list_table->get_send_time() ); ?>
				</div>
				<div class="b4b-field b4b-field-wrapper-frequency_value b4b-field-wrapper-frequency_value-weekly">
					<label for="b4b-field-frequency_value-weekly"><?php _e( 'Send on', 'b4b-theme-support' ); ?></label>
					<select name="frequency_value" disabled="disabled" id="b4b-field-frequency_value-weekly">
						<?php foreach ( $wp_list_table->get_days() as $day_key => $day_label ) : ?>
							<option value="<?php echo esc_html( $day_key ); ?>"><?php echo esc_html( $day_label ); ?></option>
						<?php endforeach; ?>
					</select>
					<label><?php printf( __( 'at %s', 'b4b-theme-support' ), $wp_list_table->get_send_time() ); ?></label>
				</div>
				<div class="b4b-field b4b-field-wrapper-frequency_value b4b-field-wrapper-frequency_value-daily">
					<?php _e( 'Send every', 'b4b-theme-support' ); ?>
					<input type="number" name="frequency_value" value="1" disabled="disabled" required="required" min="1" max="30" />
					<?php _e( 'day(s)', 'b4b-theme-support' ); ?> <?php printf( __( 'at %s', 'b4b-theme-support' ), $wp_list_table->get_send_time() ); ?>
				</div>
			</div>
			<div class="b4b-mail-popup-footer">
				<?php wp_nonce_field( 'b4b_mail_edit_save', 'nonce_field' ); ?>
				<input type="hidden" name="action" value="b4b_mail_edit_save" />
				<input type="hidden" name="id" id="b4b-field-id" value="" />

				<input type="button" class="button button-large button-secondary" id="b4b-mail-popup-cancel" value="<?php _e( 'Cancel', 'b4b-theme-support' ); ?>">
				<input type="submit" class="button button-large button-primary" id="b4b-mail-popup-submit" value="<?php _e( 'Save', 'b4b-theme-support' ); ?>">
			</div>
		</form>
    </div>
</div><!-- .b4b-mail-popup -->


<script>
document.addEventListener( 'DOMContentLoaded', function () {
    var $ = jQuery,
		$popupSendConfirmation = $( '#b4b-mail-popup-send-confirmation' ),
		$popupEdit = $( '#b4b-mail-popup' ),
        $form = $( '#b4b-mail-form-edit' );


	/**
	 * Open confirmation popup
	 */
	$( '.wp-list-table.mails' ).on( 'click', '.b4b-send-confirmation', function ( event ) {
		event.preventDefault();

		$( '#b4b-mail-popup-send-confirmation-confirm' ).attr( 'href', $( this ).attr( 'href' ) );
		$popupSendConfirmation.addClass( 'show' );
	} );

	/**
	 * Close confirmation popup
	 */
	$( '#b4b-mail-popup-send-confirmation-cancel' ).click( function () {
		$popupSendConfirmation.removeClass( 'show' );
	} );


	/**
	 * Hide/show controls based on frequency type
	 * @returns {void}
	 */
	function frequencyValueVisibility()
	{
		var frequencyType = $( '#b4b-field-frequency_type' ).val();
		$( '.b4b-field-wrapper-frequency_value' ).hide().find( 'input, select' ).prop( 'disabled', true );
		$( '.b4b-field-wrapper-frequency_value-' + frequencyType ).show().find( 'input, select' ).prop( 'disabled', false );
	}

	/**
	 * Load data to form
	 */
	$( '.b4b-mail-edit' ).click( function () {
		var $tr = $( this ).closest( 'tr' );

		$( '#b4b-field-id' ).val( $tr.data( 'id' ) );
		$( '#b4b-popup-title' ).text( $tr.data( 'title' ) );
		$( '#b4b-field-email_subject' ).val( $tr.data( 'email_subject' ) );

		var frequency_type = $tr.data( 'frequency_type' );
		$( '#b4b-field-frequency_type' ).val( frequency_type );
		frequencyValueVisibility();

		$( '.b4b-field-wrapper-frequency_value-' + frequency_type ).find( 'input, select' ).val( $tr.data( 'frequency_value' ) );

		$popupEdit.addClass( 'show' );
	} );

	/**
	 * Handle cancel button
	 */
	$( '#b4b-mail-popup-cancel' ).click( function () {
		$popupEdit.removeClass( 'show' );
	} );

	/**
	 * Handle frequency changes
	 */
	$( '#b4b-field-frequency_type' ).change( function () {
		frequencyValueVisibility();
	} );

	/**
	 * Handle form submit
	 */
    $form.on( 'submit', function ( event ) {
        event.preventDefault();

        $.ajax( {
            url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
            type: 'POST',
            dataType: 'json',
			data: $form.serialize(),
            success: function ( response ) {
                window.location = response.redirect;
            },
            error: function ( XMLHttpRequest, textStatus, exception ) {
                console.log( 'Ajax failure: ' + XMLHttpRequest.statusText );
            },
            async: true
        } );

    } );

} );
</script>
