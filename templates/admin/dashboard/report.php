<?php

/**
 * @var $min_year int
 * @var $max_year int
 * @var $selected_year int
 *
 * @var $accounts array
 * @var $scramble_captains array
 * @var $scramble_captain_id int
 * @var $event_types array
 *
 * @var $donation_total string
 * @var $count_goal_not_meet string
 * @var $count_missing_scramble_captain string
 * @var $count_missing_shirt_hat_size string
 */


function b4b_admin_report_make_link( $args = [], $unset_args = [] )
{
	$args = array_merge( $_GET, $args );
	foreach ( $unset_args as $unset_arg ) {
		if ( isset( $args[$unset_arg] ) ) {
			unset( $args[$unset_arg] );
		}
	}

	$query_string = http_build_query( $args );
	return admin_url( "?$query_string#donation-table" );
}

function b4b_admin_report_make_order_link( $order_by, $order = '' )
{
	if ( empty( $order ) ) {
		$get_order_by = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'name';
		$order = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'asc';

		if ( $get_order_by === $order_by ) {
			// Toggle sort order
			$order = ( $order === 'asc' ) ? 'desc' : 'asc';
		} else {
			// For string columns default order is asc, for numeric columns default order is desc
			$order = in_array( $order_by, ['name', 'event_scramble_captain_name'] ) ? 'asc' : 'desc';
		}
	}

	return b4b_admin_report_make_link( [
		'orderby' => $order_by,
		'order'   => $order,
	] );
}

function b4b_report_order_class( $column_name )
{
	$class = in_array( $column_name, ['name', 'event_scramble_captain_name'] ) ? 'sortable desc' : 'sortable asc';

	if ( isset( $_GET['orderby'] ) ) {
		$order_by = sanitize_text_field( $_GET['orderby'] );
		$order    = sanitize_text_field( $_GET['order'] );

		if ( $order_by === $column_name ) {
			$class = "sortable sorted $order";
		}
	} else {
		if ( $column_name === 'name' ) {
			$class = 'sortable sorted name asc';
		}
	}

	return $class;
}

?>

<h2><?php _e('B4B Golfers Report'); ?></h2>

<style>
#welcome-panel {
	display: block !important;
}
#welcome-panel .welcome-panel-close {
	display: none;
}

.b4b-admin-tabs {
	overflow: hidden;
	margin: 2em 0;
}
.b4b-admin-tabs::after {
	content: "";
	display: block;
	width: 100%;
	height: 43px;
	border-bottom: 1px solid #0073aa;
}
.b4b-admin-tabs > li {
	float: left;
	margin: 0;
	padding: 0;
	height: 43px;
}
.b4b-admin-tabs > li > a,
.b4b-admin-tabs > li > a:active,
.b4b-admin-tabs > li > a:focus,
.b4b-admin-tabs > li > a:visited {
	float: left;
	padding: .7em 3em;
	box-shadow: none;
	color: #32373c;
	outline: none;
}
.b4b-admin-tabs > li > a:hover {
	background: #f1f1f1;
	color: #0073aa;
}
.b4b-admin-tabs > li > a.current {
	background: #fff;
	border: 1px solid #0073aa;
	border-bottom-color: #fff;
	color: #0073aa;
}

.b4b-admin-filters {
	display: flex;
	margin: 3em 0 5em;
	max-width: 1100px;
}
.b4b-admin-filters > .b4b-admin-filter-col {
	flex-grow: 1;
	padding-right: 1em;
	width: 40%;
}
.b4b-admin-filters > .b4b-admin-filter-col > label.alignleft {
	padding-right: 2em;
	line-height: 30px;
}
.b4b-admin-filters > .b4b-admin-filter-col select {
	width: 100%;
}
.b4b-admin-filters > .b4b-admin-filter-button {
	flex-grow: 0;
	padding-top: 2em;
}
.b4b-admin-filters > .b4b-admin-filter-button .button {
	margin: 0 !important;
	padding: .5em;
	width: 150px;
	line-height: 2;
	background: #249cd3;
	color: #fff;
	font-size: 1.1em;
	font-weight: 600;
	text-transform: uppercase;
}
.b4b-admin-filters .b4b-admin-filter-label {
	display: block;
	padding-bottom: 1em;
	font-weight: bold;
}

.b4b-report-stats {
	overflow: hidden;
	display: flex;
	margin-bottom: 3em;
	max-width: 1100px;
}
.b4b-report-stats-item {
	flex-grow: 1;
	margin-right: 2em;
	padding: 10px;
	width: 200px;
	background: #f2f2f2;
	border: 1px solid #ccd0d4;
	color: inherit;
	text-align: center;
}
.b4b-report-stats-item:last-child {
	margin-right: 0;
}
.b4b-report-stats-item > h4 {
	margin: 0 0 1em;
	font-size: 1.1em;
}
.b4b-report-stats-item > span {
	font-size: 2.5em;
	font-weight: bold;
}
.b4b-report-stats-item > span > .light {
	color: #999;
	font-weight: normal;
}

.b4b-report-table-prefix-left {
	float: left;
	font-size: 1.1em;
}
.b4b-report-table-prefix-right {
	float: right;
	font-size: 1.1em;
}
.b4b-admin-table-empty {
	padding: 5em;
	text-align: center;
}

#welcome-panel .wp-list-table th {
	padding: 0;
	line-height: 30px;
	text-align: center;
}
#welcome-panel .wp-list-table th.column-username,
#welcome-panel .wp-list-table th.column-event-100-holes,
#welcome-panel .wp-list-table th.column-event-scramble {
	text-align: left;
}
#welcome-panel .wp-list-table th.column-username,
#welcome-panel .wp-list-table th.column-contact {
	max-width: 200px;
}
#welcome-panel .wp-list-table th.donation-event-100-holes {
	min-width: 150px;
}
#welcome-panel .wp-list-table th.column-event-scramble {
	min-width: 200px;
}

#welcome-panel .wp-list-table th a,
#welcome-panel .wp-list-table tfoot th span {
	padding: 8px;
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
#welcome-panel .wp-list-table th span.sorting-indicator:before {
	font-size: 30px;
	line-height: 8px;
}

#welcome-panel .wp-list-table td {
	vertical-align: middle;
}

strong.red,
span.red {
	font-weight: bold;
	color: #cc0000;
}
.red-light {
	color: #cc0000;
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

<div class="b4b-admin-report">
	<ul class="b4b-admin-tabs">
		<?php for ( $year = $max_year; $year >= $min_year; $year-- ) : ?>
			<li>
				<a class="<?php echo ($selected_year == $year) ? 'current' : '' ?>" href="<?php echo b4b_admin_report_make_link( [ 'filter' => $year ] ) ?>">
					<?php echo $year ?>
				</a>
			</li>
		<?php endfor ?>
	</ul>
	<form method="get">
		<div class="b4b-admin-filters">
			<div class="actions b4b-admin-filter-col">
				<label class="b4b-admin-filter-label">Type</label>
				<label class="alignleft">
					<input type="checkbox" name="event_types[]" value="event_scramble" <?php echo in_array( 'event_scramble', $event_types ) ? 'checked="checked"' : '' ?>>
					<span class="alignright">Scramble &nbsp;</span>
				</label>
				<label class="alignleft ">
					<input type="checkbox" name="event_types[]" value="event_100_holes" <?php echo in_array( 'event_100_holes', $event_types ) ? 'checked="checked"' : '' ?>>
					<span class="alignright">100 Hole &nbsp;</span>
				</label>
				<label class="alignleft">
					<input type="checkbox" name="event_types[]" value="event_party" <?php echo in_array( 'event_party', $event_types ) ? 'checked="checked"' : '' ?>>
					<span class="alignright">Tickets Purchased &nbsp;</span>
				</label>
				<input type="hidden" name="filter" value="<?php echo $selected_year ?>">
			</div>
			<div class="actions b4b-admin-filter-col">
				<label class="b4b-admin-filter-label">Scramble Captain</label>
				<select name="user_id" class="select2 enhanced">
					<option value="">All</option>
					<?php foreach ( $scramble_captains as $sc_id => $sc_name ) : ?>
						<option value="<?php echo $sc_id ?>" <?php if ( $scramble_captain_id === $sc_id ) echo 'selected="selected"' ?>>
							<?php echo $sc_name ?>
						</option>
					<?php endforeach ?>
				</select>
			</div>
			<div class="b4b-admin-filter-button">
				<input type="submit" id="post-query-submit" class="button" value="Filter" />
			</div>
		</div>
	</form>
</div>

<div class="b4b-report-stats">
	<div class="b4b-report-stats-item">
		<h4>100 Hole Money Raised</h4>
		<span><?php echo wc_price( $donation_total ); ?></span>
	</div>
	<a href="<?php echo esc_url( b4b_admin_report_make_link( [ 'filter-missing' => '100-golfers' ] ) ) ?>" class="b4b-report-stats-item">
		<h4>100 Hole Golfers Short of Their Goal</h4>
		<span><?php echo $count_goal_not_meet; ?></span>
	</a>
	<a href="<?php echo esc_url( b4b_admin_report_make_link( [ 'filter-missing' => 'scramble-captain' ] ) ) ?>" class="b4b-report-stats-item">
		<h4>Missing Scramble Player Info</h4>
		<span><?php echo $count_missing_scramble_captain; ?></span>
	</a>
	<a href="<?php echo esc_url( b4b_admin_report_make_link( [ 'filter-missing' => 'hat-tshirt' ] ) ) ?>" class="b4b-report-stats-item">
		<h4>Missing Shirt or Hat Sizes</h4>
		<span><?php echo $count_missing_shirt_hat_size; ?></span>
	</a>
</div>

<div id="b4b-report-table">
	<div class="b4b-report-table-prefix-left">
		<?php echo count( $accounts ) ?> results
	</div>
	<div class="b4b-report-table-prefix-right">
		<a href="<?php echo b4b_admin_report_make_link( [ 'export' => 'golfers' ] ) ?>">
			Export Golfers Report (.csv)
		</a>
		|
		<a href="<?php echo b4b_admin_report_make_link( [ 'export' => 'donations', 'event_types[]' => 'event_100_holes' ] ) ?>">
			Export Donations Report (.csv)
		</a>
	</div>

	<table class="wp-list-table widefat fixed striped">
		<tbody>
			<?php if ( empty( $accounts ) ) : ?>
				<tr>
					<td colspan="8">
						<p class="b4b-admin-table-empty">No results found.</p>
					</td>
				</tr>
			<?php else: ?>
				<?php foreach ( $accounts as $account ) : ?>

					<?php
					if ( $account['user_id'] !== 0 ) {
						$user_edit_url = $account['user_edit_url'];
						$swag_edit_url = $account['user_edit_url'] . '#fieldset-customer';
					} else {
						$user_edit_url = $account['user_edit_url'] . '#woocommerce-order-scramble-team';
						$swag_edit_url = $user_edit_url;
					}
					?>

					<tr id="account-<?php echo $account['user_id'] ?>">
						<td class="username column-username">
							<?php echo get_avatar( $account['user_id'], 50 ) ?>
							<a href="<?php echo esc_url( $user_edit_url ) ?>" target="_blank">
								<?php if ( $account['name'] !== '—' ) : ?>
									<strong><?php echo $account['name'] ?></strong>
								<?php else : ?>
									<span class="red-light">Add player name</span>
								<?php endif ?>
							</a>
						</td>
						<td class="event-100-holes column-event-100-holes">
							<?php if ( $account['event_100_holes'] === true ) : ?>
								<strong>Yes </strong><a href="<?php echo $account['event_100_holes_order_url'] ?>" target="_blank">
									<?php if ( $account['event_100_holes_paid'] === true ) : ?>
										<strong><?php echo wc_price( $account['event_100_holes_fee'] ) ?></strong>
									<?php else : ?>
										<strong class="red"><?php echo wc_price( 0 ) ?></strong>
									<?php endif ?>
								</a>
							<?php else : ?>
								No
							<?php endif ?>
						</td>
						<td class="donation-event-100-holes column-donation-event-100-holes num">
							<?php if ( $account['event_100_holes'] === true ) : ?>
								<a href="<?php echo b4b_admin_report_make_link( [ 'dig' => $account['user_id'] ] ) ?>">
									<span class="<?php echo ( $account['event_100_holes_money_raised'] >= $account['event_100_holes_donation_goal'] ) ? '' : 'red' ?>">
										<?php echo wc_price( $account['event_100_holes_money_raised'] ) ?>
									</span>
								</a>
							<?php else : ?>
								—
							<?php endif ?>
						</td>
						<td class="column-event-scramble">
							<?php if ( $account['event_scramble'] === true ) : ?>
								<?php if ( $account['event_scramble_captain'] ) : ?>
									<a href="<?php echo $account['event_scramble_order_url'] ?>" target="_blank">
										<span><?php echo $account['event_scramble_captain_name'] ?></span>
										<?php if ( $account['event_scramble_paid'] === true ) : ?>
											<strong><?php echo wc_price( $account['event_scramble_fee'] ) ?></strong>
										<?php else : ?>
											<strong class="red"><?php echo wc_price( 0 ) ?></strong>
										<?php endif ?>
									</a>
								<?php else : ?>
									<a href="<?php echo $account['event_scramble_order_url'] ?>">
										<span><?php echo $account['event_scramble_captain_name'] ?></span>
									</a>
								<?php endif ?>
							<?php else : ?>
								Not Playing
							<?php endif ?>
						</td>
						<td class="column-tshirt-size num">
							<?php if ( $account['event_100_holes'] === true || $account['event_scramble'] === true ) : ?>
								<a href="<?php echo esc_url( $swag_edit_url ) ?>" target="_blank">
									<?php if ( $account['tshirt_size'] ) : ?>
										<?php echo $account['tshirt_size'] ?>
									<?php else : ?>
										<span class="red-light">Add shirt size</span>
									<?php endif ?>
								</a>
							<?php else : ?>
								—
							<?php endif ?>
						</td>
						<td class="column-hat-size num">
							<?php if ( $account['event_100_holes'] === true || $account['event_scramble'] === true ) : ?>
								<a href="<?php echo esc_url( $swag_edit_url ) ?>" target="_blank">
									<?php if ( $account['hat_size'] ) : ?>
										<?php echo $account['hat_size'] ?>
									<?php else : ?>
										<span class="red-light">Add hat size</span>
									<?php endif ?>
								</a>
							<?php else : ?>
								—
							<?php endif ?>
						</td>
						<td class="column-tickets-purchased-quantity num">
							<?php if ( $account['event_party'] === true ) : ?>
								<?php echo $account['event_party_tickets_quantity'] ?>
							<?php else : ?>
								—
							<?php endif ?>
						</td>
						<td class="column-contact">
							<a href="mailto: <?php echo $account['contact_email'] ?>"><?php echo $account['contact_email'] ?></a>
							<br /><?php echo $account['contact_phone'] ?>
						</td>
					</tr>
					<?php if ( isset( $account['event_100_holes_donations_report'] ) ) : ?>
						<tr id="donation-table">
							<td>
								<a href="<?php echo b4b_admin_report_make_link( [], [ 'dig' ] ) ?>" class="alignright"><strong>Close</strong></a>
							</td>
							<td colspan="7" style="width: 100%">
								<div>
									<legend>
										<h3>Donations for <?php echo $account['name'] ?></h3>
									</legend>
									<table class="wp-list-table widefat">
										<tbody>
											<?php foreach ( $account['event_100_holes_donations_report'] as $donation ) : ?>
												<tr>
													<td class="column-donation-date">
														<?php echo date_i18n( get_option( 'date_format' ), strtotime( $donation['date'] ) ) ?>
													</td>
													<td>
														<a href="<?php echo $donation['order_link'] ?>" target="_blank">
															<?php echo $donation['donor_name'] ?>
														</a>
													</td>
													<td>
														<?php echo $donation['message'] ?>
													</td>
													<td>
														<?php echo wc_price( $donation['amount'] ) ?>
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
			<?php endif; ?>
		</tbody>
		<thead>
			<tr>
				<th class="username column-username <?php echo esc_attr( b4b_report_order_class( 'name' ) ) ?>">
					<a href="<?php echo esc_url( b4b_admin_report_make_order_link( 'name' ) ) ?>">
						<span>Name</span>
						<span class="sorting-indicator"></span>
					</a>
				</th>
				<th class="event-100-holes column-event-100-holes <?php echo esc_attr( b4b_report_order_class( 'event_100_holes_fee' ) ) ?>">
					<a href="<?php echo esc_url( b4b_admin_report_make_order_link( 'event_100_holes_fee' ) ) ?>">
						<span>100 Holes</span>
						<span class="sorting-indicator"></span>
					</a>
				</th>
				<th class="donation-event-100-holes column-donation-event-100-holes num <?php echo esc_attr( b4b_report_order_class( 'event_100_holes_money_raised' ) ) ?>">
					<a href="<?php echo esc_url( b4b_admin_report_make_order_link( 'event_100_holes_money_raised' ) ) ?>">
						<span>Money Raised</span>
						<span class="sorting-indicator"></span>
					</a>
				</th>
				<th class="column-event-scramble <?php echo esc_attr( b4b_report_order_class( 'event_scramble_captain_name' ) ) ?>">
					<a href="<?php echo esc_url( b4b_admin_report_make_order_link( 'event_scramble_captain_name' ) ) ?>">
						<span>Scramble Captain</span>
						<span class="sorting-indicator"></span>
					</a>
				</th>
				<th class="column-tshirt-size num"><span>Shirt Size</span></th>
				<th class="column-hat-size num"><span>Hat Size</span></th>
				<th class="column-tickets-purchased-quantity num <?php echo esc_attr( b4b_report_order_class( 'event_party_tickets_quantity' ) ) ?>">
					<a href="<?php echo esc_url( b4b_admin_report_make_order_link( 'event_party_tickets_quantity' ) ) ?>">
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
			exportTableToCSV.apply(this, [$('#b4b-report-table table'), 'b4b-report-<?php echo date( 'Y-m-d' ) ?>-<?php echo time() ?>.csv']);
		});
	});
</script>
