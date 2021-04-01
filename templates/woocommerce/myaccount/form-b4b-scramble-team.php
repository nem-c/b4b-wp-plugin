<?php

/**
 * @var array $b4b_scramble_team_members
 * @var $order
 * @var $nonce
 */

?>
<h2 class="woocommerce-order-details__title">My scramble team</h2>
<form method="post" class="my-team-form">
	<div class="panel-wrap woocommerce">

		<fieldset>
			<legend><?php _e( 'My details', B4B_TEXT_DOMAIN ) ?></legend>
			<div class="edit-scramble">
				<div class="holder-player">
					<div class="row">
							<?php woocommerce_form_field( 'b4b_tshirt_size', [
								'id'      => 'b4b_tshirt_size',
								'type'    => 'select',
								'label'   => __( 'T-Shirt size', B4B_TEXT_DOMAIN ),
								'class'   => [ 'form-row', 'form-row-first' ],
								'options' => b4b_tshirt_sizes(),
							], get_user_meta( $order->get_customer_id(), 'b4b_tshirt_size', true ) ); ?>
							<?php woocommerce_form_field( 'b4b_hat_size', [
								'id'      => 'b4b_hat_size',
								'type'    => 'select',
								'label'   => __( 'Hat size', B4B_TEXT_DOMAIN ),
								'class'   => [ 'form-row', 'form-row-last' ],
								'options' => b4b_hat_sizes(),
							], get_user_meta( $order->get_customer_id(), 'b4b_hat_size', true ) ); ?>
					</div>
				</div>
			</div>
		</fieldset>

		<fieldset>
			<legend><?php _e( 'Team details', B4B_TEXT_DOMAIN ) ?></h3></legend>
			<div class="team-group edit-scramble">
				<?php foreach ($b4b_scramble_team_members as $position => $member) : ?>
					<div class="holder-player">
						<h3>Team Member <?php echo $position ?></h3>
						<div class="row">
							<p class="form-field form-field-wide big">
								<label for="b4b_scramble_team_members[<?php echo $position ?>][name]">Name:</label>
								<input type="text" class="long" value="<?php echo $member["name"] ?>" name="b4b_scramble_team_members[<?php echo $position ?>][name]" id="b4b_scramble_team_members[<?php echo $position ?>][name]">
							</p>
						</div>

						<div class="row">
							<p class="form-field form-field-wide">
								<label for="b4b_scramble_team_members[<?php echo $position ?>][email]">Email:</label>
								<input type="text" class="long" value="<?php echo $member["email"] ?>" name="b4b_scramble_team_members[<?php echo $position ?>][email]" id="b4b_scramble_team_members[<?php echo $position ?>][email]">
							</p>
							<p class="form-field form-field-wide">
								<label for="b4b_scramble_team_members[<?php echo $position ?>][phone]">Phone:</label>
								<input type="text" class="long" value="<?php echo $member["phone"] ?>" name="b4b_scramble_team_members[<?php echo $position ?>][phone]" id="b4b_scramble_team_members[<?php echo $position ?>][phone]">
							</p>
						</div>
						<div class="row">
							<p class="form-field form-field-wide">
								<label for="b4b_scramble_team_members[<?php echo $position ?>][tshirt_size]">T-Shirt Size:</label>
								<select id="b4b_scramble_team_members[<?php echo $position ?>][tshirt_size]" name="b4b_scramble_team_members[<?php echo $position ?>][tshirt_size]" class="wc-enhanced-select">
									<?php foreach (b4b_tshirt_sizes() as $name => $size) : ?>
										<option value="<?php echo $name ?>" <?php echo ($member["tshirt_size"] === $name) ? "selected=\"selected\"" : "" ?>>
											<?php echo $size ?>
										</option>
									<?php endforeach ?>
								</select>
							</p>
							<p class="form-field form-field-wide">
								<label for="b4b_scramble_team_members[<?php echo $position ?>][hat_size]">Hat Size:</label>
								<select id="b4b_scramble_team_members[<?php echo $position ?>][hat_size]" name="b4b_scramble_team_members[<?php echo $position ?>][hat_size]" class="wc-enhanced-select">
									<?php foreach (b4b_hat_sizes() as $name => $size) : ?>
										<option value="<?php echo $name ?>" <?php echo ($member["hat_size"] === $name) ? "selected=\"selected\"" : "" ?>>
											<?php echo $size ?>
										</option>
									<?php endforeach ?>
								</select>
							</p>
						</div>
					</div>
				<?php endforeach ?>
			</div>
		</fieldset>
		<div class="clear"></div>
	</div>
	<div class="hidden">
		<?php echo $nonce ?>
		<input type="hidden" name="order_id" value="<?php echo $order->get_id() ?>" />
	</div>
	<div class="alignleft">
		<a href="<?php bloginfo('my-account/orders') ?>" class="woocommerce-button button edit-scramble-team">Back to orders</a>
	</div>
	<div class="alignright">
		<button class="woocommerce-button button pay" type="submit">Update</button>
	</div>
</form>