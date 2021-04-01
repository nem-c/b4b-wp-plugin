<?php

/**
 * @var $b4b_scramble_team_members array
 */

?>

<style>
	#scramble-team-table {
		width: 100%;
	}
	#scramble-team-table th {
		width: 1%;
		white-space: nowrap;
	}
	#scramble-team-table td {
		padding-left: 1em;
	}

	#scramble-team-table .form-field {
		margin-top: 0;
	}

	#scramble-team-table .form-field input[type=email],
	#scramble-team-table .form-field input[type=text],
	#scramble-team-table .form-field select,
	#scramble-team-table .form-field .select2 {
		width: 100% !important;
	}
</style>

<div class="panel-wrap woocommerce">
	<table id="scramble-team-table">

		<?php foreach ( $b4b_scramble_team_members as $position => $member ): ?>
            <tr>
                <th>P <?php echo $position ?></th>
                <td>
                    <p class="form-field form-field-wide">
                        <label for="b4b_scramble_team_members[<?php echo $position ?>][name]">Name:</label>
                        <input type="text" class="long" value="<?php echo $member["name"] ?>"
                               name="b4b_scramble_team_members[<?php echo $position ?>][name]"
                               id="b4b_scramble_team_members[<?php echo $position ?>][name]">
                    </p>
                </td>
                <td>
                    <p class="form-field form-field-wide">
                        <label for="b4b_scramble_team_members[<?php echo $position ?>][email]">Email:</label>
                        <input type="email" class="long" value="<?php echo $member["email"] ?>"
                               name="b4b_scramble_team_members[<?php echo $position ?>][email]"
                               id="b4b_scramble_team_members[<?php echo $position ?>][email]">
                    </p>
                </td>
                <td>
                    <p class="form-field form-field-wide">
                        <label for="b4b_scramble_team_members[<?php echo $position ?>][phone]">Phone:</label>
                        <input type="text" class="long" value="<?php echo $member["phone"] ?>"
                               name="b4b_scramble_team_members[<?php echo $position ?>][phone]"
                               id="b4b_scramble_team_members[<?php echo $position ?>][phone]">
                    </p>
                </td>
                <td>
                    <p class="form-field form-field-wide">
                        <label for="b4b_scramble_team_members[<?php echo $position ?>][tshirt_size]">T-Shirt Size:</label>
                        <select id="b4b_scramble_team_members[<?php echo $position ?>][tshirt_size]"
                                name="b4b_scramble_team_members[<?php echo $position ?>][tshirt_size]"
                                class="wc-enhanced-select">
							<?php foreach ( b4b_tshirt_sizes() as $name => $size ): ?>
                                <option value="<?php echo $name ?>" <?php echo ( $member["tshirt_size"] === $name ) ? "selected=\"selected\"" : "" ?>>
									<?php echo $size ?>
                                </option>
							<?php endforeach ?>
                        </select>
                    </p>
                </td>
                <td>
                    <p class="form-field form-field-wide">
                        <label for="b4b_scramble_team_members[<?php echo $position ?>][hat_size]">Hat Size:</label>
                        <select id="b4b_scramble_team_members[<?php echo $position ?>][hat_size]"
                                name="b4b_scramble_team_members[<?php echo $position ?>][hat_size]"
                                class="wc-enhanced-select">
							<?php foreach ( b4b_hat_sizes() as $name => $size ): ?>
                                <option value="<?php echo $name ?>" <?php echo ( $member["hat_size"] === $name ) ? "selected=\"selected\"" : "" ?>>
									<?php echo $size ?>
                                </option>
							<?php endforeach ?>
                        </select>
                    </p>
                </td>
            </tr>
		<?php endforeach ?>

	</table>
</div>
