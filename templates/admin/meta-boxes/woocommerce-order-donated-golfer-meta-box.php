<?php

/**
 * @var $registered_golfers array
 * @var $b4b_donated_golfer_id string
 */

?>
<div class="panel-wrap woocommerce">
    <p class="form-field form-field-wide">
        <label for="b4b_donated_golfer_id">Donated Golfer:</label>
        <select id="b4b_donated_golfer_id" name="b4b_donated_golfer_id" class="wc-enhanced-select">
			<?php foreach ( $registered_golfers as $id => $name ): ?>
                <option value="<?php echo $id ?>" <?php echo ( $b4b_donated_golfer_id === $id ) ? "selected=\"selected\"" : "" ?>>
					<?php echo $name ?>
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