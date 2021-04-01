<?php

/**
 * @var $min_year int
 * @var $max_year int
 * @var $selected_year int
 *
 * @var $accounts array
 * @var $accounts_scramble array
 * @var $scramble_captain_id int
 * @var $event_types array
 * @var $total_accounts int
 */
?>

<?php

function b4b_admin_report_make_link($args = [], $unset_args = [])
{

	$args = array_merge($_GET, $args);
	foreach ($unset_args as $unset_arg) {
		if (isset($args[$unset_arg])) {
			unset($args[$unset_arg]);
		}
	}

	$query_string = http_build_query($args);
	$scroll_to_table = 'donation-table';

	return admin_url("?$query_string#$scroll_to_table");
}

function b4b_admin_report_make_order_link($order_by, $order = "")
{
	if (empty($order) === true) {
		//check for direction in request
		$order = sanitize_text_field($_REQUEST["order"]);
		if (empty($order) === true || sanitize_text_field($_REQUEST["orderby"]) !== $order_by) {
			if (empty(sanitize_text_field($_REQUEST["orderby"])) === true && $order_by === "name") {
				$order = "asc";
			} else {
				$order = "desc";
			}
		} else {
			if ($order === "asc") {
				$order = "desc";
			} else {
				$order = "asc";
			}
		}
	}

	return b4b_admin_report_make_link([
		"orderby" => $order_by,
		"order"   => $order,
	]);
}

function b4b_report_order_class($column_name)
{
	$class = "sortable desc";

	if (isset($_REQUEST["orderby"])) {
		$order_by = sanitize_text_field($_REQUEST["orderby"]);
		$order    = sanitize_text_field($_REQUEST["order"]);
		if ($order_by === $column_name) {
			$class = "sortable sorted $order";
		}
	} else {
		if ($column_name === "name") {
			$class = "sortable sorted name desc";
		}
	}

	return $class;
}

?>

<h2><?php _e('B4B Golfers Report'); ?></h2>
<p class="about-description"><?php _e(''); ?></p>

<div class="b4b-admin-report">
	<ul class="subsubsub">
		<li>
			<strong>Filter by Year</strong>
		</li>
		<?php for ($year = $max_year; $year >= $min_year; $year--) : ?>
			<li>
				|
				<a class="<?php echo ($selected_year == $year) ? "current" : "" ?>" href="<?php echo b4b_admin_report_make_link(["filter" => $year]) ?>">
					<?php echo $year ?>
					<?php if ($selected_year === $year) : ?>
						(<?php echo $total_accounts ?>)
					<?php endif ?>
				</a>
			</li>
		<?php endfor ?>
	</ul>
	<div class="tablenav top">
		<div class="alignleft actions">
			<form method="get">
				<label class="alignleft">Event Type &nbsp;</label>
				<label class="alignleft ">
					<input type="checkbox" name="event_types[]" value="event_100_holes" <?php echo (in_array("event_100_holes", $event_types)) ? "checked=\"checked\"" : "" ?>>
					<span class="alignright">100 Holes &nbsp;</span>
				</label>
				<label class="alignleft">
					<input type="checkbox" name="event_types[]" value="event_scramble" <?php echo (in_array("event_scramble", $event_types)) ? "checked=\"checked\"" : "" ?>>
					<span class="alignright">Scramble &nbsp;</span>
				</label>
				<label class="alignleft">
					<input type="checkbox" name="event_types[]" value="event_party" <?php echo (in_array("event_party", $event_types)) ? "checked=\"checked\"" : "" ?>>
					<span class="alignright">Tickets &nbsp;</span>
				</label>
				<label class="alignleft">
					<input type="checkbox" name="missing_data" value="only" <?php echo (($_REQUEST["missing_data"] ?? "") === "only") ? "checked=\"checked\"" : "" ?>>
					<span class="alignright">Incomplete data &nbsp;</span>
				</label>
				<input type="hidden" name="filter" value="<?php echo $selected_year ?>">
				<input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter" />
			</form>
		</div>
		<div class="alignleft actions">
			<form method="get">
				<label class="alignleft">Scramble Captain &nbsp;</label>
				<select name="user_id" class="select2 enhanced">
					<option value="">All</option>
					<?php foreach ($accounts_scramble as $account) : ?>
						<option value="<?php echo $account["user_id"] ?>" <?php echo ($scramble_captain_id === $account["user_id"]) ? "selected=\"selected\"" : "" ?>>
							<?php echo $account["name"] ?>
						</option>
					<?php endforeach ?>
				</select>
				<input type="hidden" name="filter" value="<?php echo $selected_year ?>">
				<input type="submit" name="filter_action" id="post-query-submit" class="button" value="Show" />
			</form>
		</div>

		<div class="tablenav-pages one-page">
			<span class="displaying-num">
				showing <?php echo count($accounts) ?> / <?php echo $total_accounts ?> golfers for <?php echo $selected_year ?>
			</span>
			<br />
			<a href="<?php echo b4b_admin_report_make_link(["export" => "golfers"]) ?>" class="">
				Export Golfers Report (.csv)
			</a>
			|
			<a href="<?php echo b4b_admin_report_make_link([
							"export"        => "donations",
							"event_types[]" => "event_100_holes"
						]) ?>" class="">Export Donations Report (.csv)</a>
		</div>
	</div>
</div>
<div id="b4b-report-table">
	<table class="wp-list-table widefat fixed striped">
		<tbody>
			<?php foreach ($accounts as $account) : ?>
				<tr id="account-<?php echo $account["user_id"] ?>">
					<td class="username column-username">
						<?php echo get_avatar($account["user_id"], 50) ?>
						<?php if (empty($account["user_edit_url"]) === false) : ?>
							<a href="<?php echo $account["user_edit_url"] ?>" target="_blank">
								<strong><?php echo $account["name"] ?></strong>
							</a>
						<?php else : ?>
							<strong><?php echo $account["name"] ?></strong>
						<?php endif ?>
					</td>
					<td class="event-100-holes column-event-100-holes">
						<?php if ($account["event_100_holes"] === true) : ?>
							<a href="<?php echo $account["event_100_holes_order_url"] ?>" target="_blank">
								<?php if ($account["event_100_holes_paid"] === true) : ?>
									<strong class=""><?php echo wc_price($account["event_100_holes_fee"]) ?></strong>
								<?php else : ?>
									<strong class="red"><?php echo wc_price(0) ?></strong>
								<?php endif ?>
							</a>
						<?php else : ?>
							—
						<?php endif ?>
					</td>
					<td class="donation-event-100-holes column-donation-event-100-holes num">
						<?php if ($account["event_100_holes"] === true) : ?>
							<a href="<?php echo b4b_admin_report_make_link(["dig" => $account["user_id"]]) ?>">
								<span class="<?php echo ($account["event_100_holes_donation_goal_met"] === false) ? "red" : "" ?>">
									<?php echo wc_price($account["event_100_holes_money_raised"]) ?>
								</span>
							</a>
						<?php else : ?>
							—
						<?php endif ?>
					</td>
					<td class="column-event-scramble">
						<?php if ($account["event_scramble"] === true) : ?>
							<?php if ($account["event_scramble_captain"]) : ?>
								<a href="<?php echo $account["event_scramble_order_url"] ?>" target="_blank">
									<span><?php echo $account["event_scramble_captain_name"] ?></span>
									<?php if ($account["event_scramble_paid"] === true) : ?>
										<strong class=""><?php echo wc_price($account["event_scramble_fee"]) ?></strong>
									<?php else : ?>
										<strong class="red"><?php echo wc_price(0) ?></strong>
									<?php endif ?>
								</a>
							<?php else : ?>
								<a href="<?php echo $account["event_scramble_order_url"] ?>">
									<span><?php echo $account["event_scramble_captain_name"] ?></span>
								</a>
							<?php endif ?>
						<?php else : ?>
							—
						<?php endif ?>
					</td>
					<td class="column-tshirt-size num">
						<?php if ($account["event_100_holes"] === true || $account["event_scramble"] === true) : ?>
							<?php echo $account["tshirt_size"] ?>
						<?php else : ?>
							—
						<?php endif ?>
					</td>
					<td class="column-hat-size num">
						<?php if ($account["event_100_holes"] === true || $account["event_scramble"] === true) : ?>
							<?php echo $account["hat_size"] ?>
						<?php else : ?>
							—
						<?php endif ?>
					</td>
					<td class="column-tickets-purchased-quantity num">
						<?php if ($account["event_party"] === true) : ?>
							<?php echo $account["event_party_tickets_quantity"] ?>
						<?php else : ?>
							—
						<?php endif ?>
					</td>
					<td class="column-contact">
						<a href="mailto: <?php echo $account["contact_email"] ?>"><?php echo $account["contact_email"] ?></a><br />
						<?php echo $account["contact_phone"] ?>
					</td>
				</tr>
				<?php if (isset($account["event_100_holes_donations_report"])) : ?>
					<tr id="donation-table">
						<td>
							<a href="<?php echo b4b_admin_report_make_link([], ["dig"]) ?>" class="alignright"><strong>Close</strong></a>
						</td>
						<td colspan="7" style="width: 100%">
							<div>
								<legend>
									<h3>Donations for <?php echo $account["name"] ?></h3>
								</legend>
								<table class="wp-list-table widefat">
									<tbody>
										<?php foreach ($account["event_100_holes_donations_report"] as $donation) : ?>
											<tr>
												<td class="column-donation-date">
													<?php echo date_i18n(get_option("date_format"), strtotime($donation["date"])) ?>
												</td>
												<td>
                                                    <a href="<?php echo $donation["order_link"] ?>" target="_blank">
													    <?php echo $donation["donor_name"] ?>
                                                    </a>
												</td>
												<td>
													<?php echo $donation["message"] ?>
												</td>
												<td>
													<?php echo wc_price($donation["amount"]) ?>
												</td>
											</tr>
										<?php endforeach ?>
									</tbody>
									<thead>
										<tr>
											<th class="column-donation-date"><span>Date</span></th>
											<th class="column-donation-donor-name"><span>Name</span></th>
											<th class="column-donation-message"><span>Message</span></th>
											<th class="column-donation-amount"><span>Amount</span></th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th class="column-donation-date"><span>Date</span></th>
											<th class="column-donation-donor-name"><span>Name</span></th>
											<th class="column-donation-message"><span>Message</span></th>
											<th class="column-donation-amount"><span>Amount</span></th>
										</tr>
									</tfoot>
								</table>
							</div>
						</td>
					</tr>
				<?php endif ?>
			<?php endforeach ?>
		</tbody>
		<thead>
			<tr>
				<th class="username column-username <?php echo b4b_report_order_class("name") ?>">
					<a href="<?php echo b4b_admin_report_make_order_link("name") ?>">
						<span>Name</span>
						<span class="sorting-indicator"></span>
					</a>
				</th>
				<th class="event-100-holes column-event-100-holes <?php echo b4b_report_order_class("event_100_holes_fee") ?>">
					<a href="<?php echo b4b_admin_report_make_order_link("event_100_holes_fee") ?>">
						<span>100 Holes</span>
						<span class="sorting-indicator"></span>
					</a>
				</th>
				<th class="donation-event-100-holes column-donation-event-100-holes num <?php echo b4b_report_order_class("event_100_holes_money_raised") ?>">
					<a href="<?php echo b4b_admin_report_make_order_link("event_100_holes_money_raised") ?>">
						<span>Money Raised</span>
						<span class="sorting-indicator"></span>
					</a>
				</th>
				<th class="column-event-scramble">
					<a href="<?php echo b4b_admin_report_make_order_link("event_scramble_fee") ?>">
						<span>Scramble Captian</span>
						<span class="sorting-indicator"></span>
					</a>
				</th>
				<th class="column-tshirt-size num"><span>Shirt Size</span></th>
				<th class="column-hat-size num"><span>Hat Size</span></th>
				<th class="column-tickets-purchased-quantity num <?php echo b4b_report_order_class("event_party_tickets_quantity") ?>">
					<a href="<?php echo b4b_admin_report_make_order_link("event_party_tickets_quantity") ?>">
						<span>Tickets</span>
						<span class="sorting-indicator"></span>
					</a>
				</th>
				<th class="column-contact"><span>Contact</span></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="username column-username"><span>Name</span></th>
				<th class="event-100-holes column-event-100-holes"><span>100 Holes</span></th>
				<th class="donation-event-100-holes column-donation-event-100-holes num"><span>Money Raised</span></th>
				<th class="column-event-scramble"><span>Scramble Captain</span></th>
				<th class="column-tshirt-size num"><span>Shirt Size</span></th>
				<th class="column-hat-size num"><span>Hat Size</span></th>
				<th class="column-tickets-purchased-quantity num"><span>Tickets Purchased</span></th>
				<th class="column-contact"><span>Contact</span></th>
			</tr>
		</tfoot>
	</table>
</div>
<br class="clear" />

<style type="text/css">
	#welcome-panel .wp-list-table th {
		padding: 0;
		line-height: 30px;
		text-align: center;
	}

	#welcome-panel .wp-list-table th a {
		line-height: 30px;
		color: #356AA0;
	}

	#welcome-panel .wp-list-table th a:hover {
		color: #282a2b;
	}

	#welcome-panel .wp-list-table th span {
		float: none;
		display: inline-block;
	}

	#welcome-panel .wp-list-table th span.sorting-indicator {
		line-height: 1px;
		float: none;
		display: inline-block;
	}

	#welcome-panel .wp-list-table td {
		vertical-align: middle;
	}

	#welcome-panel .wp-list-table tfoot td {
		text-align: center;
	}

	/* Username  column */
	#welcome-panel .wp-list-table th.column-username,
	#welcome-panel .wp-list-table th.column-contact,
	#welcome-panel .wp-list-table tfoot th.column-username,
	#welcome-panel .wp-list-table td.column-username,
	#welcome-panel .wp-list-table td.column-contact,
	#welcome-panel .wp-list-table tfoot th.column-contact {
		max-width: 200px;
		text-align: left;
	}

	#welcome-panel .wp-list-table th.column-contact,
	#welcome-panel .wp-list-table tfoot th.column-contact,
	#welcome-panel .wp-list-table td.column-contact {
		text-align: center;
	}

	#welcome-panel .wp-list-table th.column-event-100-holes,
	#welcome-panel .wp-list-table tfoot th.column-event-100-holes,
	#welcome-panel .wp-list-table th.donation-event-100-holes,
	#welcome-panel .wp-list-table tfoot th.donation-event-100-holes,
	#welcome-panel .wp-list-table td.column-event-100-holes,
	#welcome-panel .wp-list-table td.donation-event-100-holes {
		min-width: 150px;
		text-align: center;
	}

	#welcome-panel .wp-list-table th.column-event-scramble,
	#welcome-panel .wp-list-table td.column-event-scramble {
		min-width: 200px;
		text-align: center;
	}

	strong.red,
	span.red {
		font-weight: bold;
		color: #CC0000;
	}

	/* Table footer */
	#welcome-panel .wp-list-table tfoot th span {
		padding: 8px;
	}

	#donation-table legend {
		margin-bottom: 15px;
	}

	/* Responsive */
	@media (max-width: 1300px) {
		.column-username img {
			display: none;
		}
	}
</style>

<script type="text/javascript">
	//http://stackoverflow.com/a/1496863
	function replaceNbsps(str) {
		var re = new RegExp(String.fromCharCode(160), "g");
		return str.replace(re, " ");
	}

	//http://jsfiddle.net/terryyounghk/KPEGU/
	function _rows2csv($rows) {
		// Temporary delimiter characters unlikely to be typed by keyboard
		// This is to avoid accidentally splitting the actual contents
		var tmpColDelim = String.fromCharCode(11); // vertical tab character
		var tmpRowDelim = String.fromCharCode(0); // null character
		// actual delimiter characters for CSV format
		var colDelim = '","';
		var rowDelim = '"\r\n"';
		return '"' + $rows.map(function(i, row) {
				var $row = jQuery(row);
				var $cols = $row.find('td,th');
				return $cols.map(function(j, col) {
					var $col = jQuery(col);
					var text = $col.text();
					text.trim();
					text = text.replace(/^\s+|\s+$/g, '');
					var a = text.replace('"', '""');
					// escape double quotes
					return replaceNbsps(a);
				}).get().join(tmpColDelim);
			}).get().join(tmpRowDelim)
			.split(tmpRowDelim).join(rowDelim)
			.split(tmpColDelim).join(colDelim) + '"\r\n';
	}

	function exportTableToCSV($table, filename) {
		var $bodyrows = $table.find('tbody tr:has(td)');
		var $headrows = $table.find('thead tr:has(th)');

		// Grab text from table into CSV formatted string
		var csv = _rows2csv($headrows);
		csv += _rows2csv($bodyrows);
		// Data URI
		console.log(csv);
		var csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);
		jQuery(this).attr({
			'download': filename,
			'href': csvData,
			'target': '_blank'
		});
	}

	jQuery(document).ready(function($) {
		$('.export-table').on('click', function(event) {
			exportTableToCSV.apply(this, [$('#b4b-report-table table'), 'b4b-report-<?php echo date("Y-m-d") ?>-<?php echo time() ?>.csv']);
		});
	});
</script>