<?php

/**
 * @var $b4b_scramble_team_members array
 */

?>

<div class="panel-wrap woocommerce">
	<?php foreach ( $b4b_scramble_team_members as $position => $member ): ?>
        <table style="width: 100%">
            <tr>
                <th style="width: 10%">P <?php echo $position ?></th>
                <td style="width: 30%">
                    <p class="form-field form-field-wide">
                        <label for="b4b_scramble_team_members[<?php echo $position ?>][name]">Name:</label>
                        <input type="text" class="long" value="<?php echo $member["name"] ?>"
                               name="b4b_scramble_team_members[<?php echo $position ?>][name]"
                               id="b4b_scramble_team_members[<?php echo $position ?>][name]">
                    </p>
                </td>
                <td style="width: 30%">
                    <p class="form-field form-field-wide">
                        <label for="b4b_scramble_team_members[<?php echo $position ?>][tshirt_size]">
                            T-Shirt Size:
                        </label>
                        <select id="b4b_scramble_team_members[<?php echo $position ?>][tshirt_size]"
                                name="b4b_scramble_team_members[<?php echo $position ?>][tshirt_size]"
                                class="wc-enhanced-select">
							<?php foreach ( b4b_tshirt_sizes() as $name => $size ): ?>
                                <option value="<?php echo $name ?>"
									<?php echo ( $member["tshirt_size"] === $name ) ? "selected=\"selected\"" : "" ?>>
									<?php echo $size ?>
                                </option>
							<?php endforeach ?>
                        </select>
                    </p>
                </td>
                <td style="width: 30%">
                    <p class="form-field form-field-wide">
                        <label for="b4b_scramble_team_members[<?php echo $position ?>][hat_size]">
                            Hat Size:
                        </label>
                        <select id="b4b_scramble_team_members[<?php echo $position ?>][hat_size]"
                                name="b4b_scramble_team_members[<?php echo $position ?>][hat_size]"
                                class="wc-enhanced-select">
							<?php foreach ( b4b_hat_sizes() as $name => $size ): ?>
                                <option value="<?php echo $name ?>"
									<?php echo ( $member["hat_size"] === $name ) ? "selected=\"selected\"" : "" ?>>
									<?php echo $size ?>
                                </option>
							<?php endforeach ?>
                        </select>
                    </p>
                </td>
            </tr>
        </table>
	<?php endforeach ?>
</div>

<style type="text/css">
    #woocommerce-order-scramble-team .select2 {
        width: 100% !important;
    }
</style>