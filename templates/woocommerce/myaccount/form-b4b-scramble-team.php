<?php

/**
 * @var $b4b_scramble_team_members array
 * @var $order
 * @var $nonce
 */

?>
<h2 class="woocommerce-order-details__title">My scramble team</h2>
<form method="post" class="my-team-form">
	<div class="panel-wrap woocommerce">
		<?php foreach ($b4b_scramble_team_members as $position => $member) : ?>
			<table class="responsive">
				<tr>
					<td>P <?php echo $position ?></td>
					<td>
						<p class="form-field form-field-wide">
							<label for="b4b_scramble_team_members[<?php echo $position ?>][name]">Name:</label>
							<input type="text" class="long" value="<?php echo $member["name"] ?>" name="b4b_scramble_team_members[<?php echo $position ?>][name]" id="b4b_scramble_team_members[<?php echo $position ?>][name]">
						</p>
					</td>
					<td>
						<p class="form-field form-field-wide">
							<label for="b4b_scramble_team_members[<?php echo $position ?>][tshirt_size]">
								T-Shirt Size:
							</label>
							<select id="b4b_scramble_team_members[<?php echo $position ?>][tshirt_size]" name="b4b_scramble_team_members[<?php echo $position ?>][tshirt_size]" class="wc-enhanced-select">
								<?php foreach (b4b_tshirt_sizes() as $name => $size) : ?>
									<option value="<?php echo $name ?>" <?php echo ($member["tshirt_size"] === $name) ? "selected=\"selected\"" : "" ?>>
										<?php echo $size ?>
									</option>
								<?php endforeach ?>
							</select>
						</p>
					</td>
					<td>
						<p class="form-field form-field-wide">
							<label for="b4b_scramble_team_members[<?php echo $position ?>][hat_size]">
								Hat Size:
							</label>
							<select id="b4b_scramble_team_members[<?php echo $position ?>][hat_size]" name="b4b_scramble_team_members[<?php echo $position ?>][hat_size]" class="wc-enhanced-select">
								<?php foreach (b4b_hat_sizes() as $name => $size) : ?>
									<option value="<?php echo $name ?>" <?php echo ($member["hat_size"] === $name) ? "selected=\"selected\"" : "" ?>>
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
	<div class="hidden">
		<?php echo $nonce ?>
		<input type="hidden" name="order_id" value="<?php echo $order->get_id() ?>" />
	</div>
	<div class="alignleft">
		<a href="<?php bloginfo(url) ?>/my-account/orders/" class="woocommerce-button button edit-scramble-team">Back to orders</a>
	</div>
	<div class="alignright">
		<button class="woocommerce-button button pay" type="submit">Update</button>
	</div>
</form>