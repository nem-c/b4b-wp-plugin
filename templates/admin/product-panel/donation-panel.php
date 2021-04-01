<?php

/**
 * @var $events array
 * @var $donation_goal integer
 * @var $donation_event_id integer
 * @var $donation_start_date string
 * @var $donation_end_date string
 */

?>

<div id="donation_product_data" class="panel woocommerce_options_panel">
    <div class="options_group show_if_donation">
        <p class="form-field show_if_donation hide">
            <label for="_b4b_donation_goal">Donation Goal</label>
            <input type="number" class="long" name="_b4b_donation_goal" value="<?php echo $donation_goal ?>"
            placeholder="Amount in $ for golfers to reach">
        </p>
        <p class="form-field show_if_donation">
            <label for="_b4b_donation_event_id">For event (100 holes events only):</label>
            <select name="_b4b_donation_event_id" id="_b4b_donation_event_id">
				<?php foreach ( $events as $event_id => $event_name_with_date ): ?>
                    <option value="<?php echo $event_id ?>"
						<?php echo ( $event_id === $donation_event_id ) ? "selected=\"selected\"" : "" ?>>
						<?php echo $event_name_with_date ?>
                    </option>
				<?php endforeach ?>
            </select>
        </p>
        <p class="form-field event_date sale_price_dates_fields show_if_donation">
            <label for="_b4b_donation_start_date"><?php echo esc_html( "Donations opened", B4B_TEXT_DOMAIN ) ?></label>
            <input type="text" class="short" name="_b4b_donation_start_date" id="_b4b_donation_start_date"
                   value="<?php echo esc_attr( $donation_start_date ) ?>"
                   placeholder="<?php echo esc_html( _x( 'Start Date&hellip;', 'placeholder', B4B_TEXT_DOMAIN ) ) ?> YYYY-MM-DD"
                   maxlength="10"
                   pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) ?>"/>

            <input type="text" class="short" name="_b4b_donation_end_date" id="_b4b_donation_end_date   "
                   value="<?php echo esc_attr( $donation_end_date ) ?>"
                   placeholder="<?php echo esc_html( _x( 'End Date&hellip;', 'placeholder', B4B_TEXT_DOMAIN ) ) ?> YYYY-MM-DD"
                   maxlength="10"
                   pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) ?>"/>
        </p>
    </div>
</div>
