<?php

/**
 * @var $event_date string
 * @var $event_registration_start_date string
 * @var $event_registration_end_date string
 * @var $event_donations_start_date string
 * @var $event_donations_end_date string
 */

?>

<div id="event_details_product_data" class="panel woocommerce_options_panel">
    <div class="options_group hide show_if_event_100_holes show_if_event_scramble show_if_event_party">
        <p class="form-field event_date sale_price_dates_fields show_if_event_100_holes show_if_event_scramble show_if_event_party">
            <label for="_b4b_event_date"><?php echo esc_html( "Event Date", B4B_TEXT_DOMAIN ) ?></label>
            <input type="text" class="short" name="_b4b_event_date" id="_b4b_event_date" value="<?php echo esc_attr( $event_date ) ?>"
                   placeholder="<?php echo esc_html( _x( 'Date&hellip;', 'placeholder', B4B_TEXT_DOMAIN ) ) ?> YYYY-MM-DD"
                   maxlength="10"
                   pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) ?>"/>
        </p>
        <hr/>
        <p class="form-field event_date sale_price_dates_fields show_if_event_100_holes show_if_event_scramble show_if_event_party">
            <label for="_b4b_event_registration_start_date"><?php echo esc_html( "Registration opened", B4B_TEXT_DOMAIN ) ?></label>
            <input type="text" class="short" name="_b4b_event_registration_start_date" id="_b4b_event_registration_start_date"
                   value="<?php echo esc_attr( $event_registration_start_date ) ?>"
                   placeholder="<?php echo esc_html( _x( 'Start Date&hellip;', 'placeholder', B4B_TEXT_DOMAIN ) ) ?> YYYY-MM-DD"
                   maxlength="10"
                   pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) ?>"/>

            <input type="text" class="short" name="_b4b_event_registration_end_date" id="_b4b_event_registration_end_date"
                   value="<?php echo esc_attr( $event_registration_end_date ) ?>"
                   placeholder="<?php echo esc_html( _x( 'End Date&hellip;', 'placeholder', B4B_TEXT_DOMAIN ) ) ?> YYYY-MM-DD"
                   maxlength="10"
                   pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) ?>"/>
        </p>
    </div>
</div>
