<?php

/**
 * @var $b4b_tshirt_size string
 * @var $b4b_hat_size string
 */

?>
<div class="panel-wrap woocommerce">
    <p class="form-field form-field-wide">
        <label for="b4b_tshirt_size">Customer T-Shirt Size:</label>
        <select id="b4b_tshirt_size" name="b4b_tshirt_size" class="wc-enhanced-select">
			<?php foreach ( b4b_tshirt_sizes() as $name => $size ): ?>
                <option value="<?php echo $name ?>" <?php echo ( $b4b_tshirt_size === $name ) ? "selected=\"selected\"" : "" ?>>
					<?php echo $size ?>
                </option>
			<?php endforeach ?>
        </select>
    </p>
    <p class="form-field form-field-wide">
        <label for="b4b_hat_size">Customer Hat Size:</label>
        <select id="b4b_hat_size" name="b4b_hat_size" class="wc-enhanced-select">
			<?php foreach ( b4b_hat_sizes() as $name => $size ): ?>
                <option value="<?php echo $name ?>" <?php echo ( $b4b_hat_size === $name ) ? "selected=\"selected\"" : "" ?>>
					<?php echo $size ?>
                </option>
			<?php endforeach ?>
        </select>
    </p>
</div>
<style type="text/css">
    #woocommerce-order-customer-data .select2 {
        width: 100% !important;
    }
</style>