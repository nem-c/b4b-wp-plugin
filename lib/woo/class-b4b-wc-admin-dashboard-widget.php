<?php


namespace B4B_Theme_Support\Lib\Woo;

use WC_Cache_Helper;
use WC_Order_Query;
use WC_Product_Query;

class B4B_WC_Admin_Dashboard_Widget {

	const MIN_YEAR = 2016;

	const ALLOWED_EVENT_TYPES = [
		'event_scramble',
		'event_100_holes',
		'event_party',
	];

	/**
	 * @var $accounts array All accounts associated (customer_id) with orders in selected year
	 */
	protected $accounts = [];

	protected $max_year;
	protected $selected_year;

	public function __construct() {
		$this->max_year      = intval( date( 'Y' ) );
		$this->selected_year = isset( $_GET['filter'] ) ? intval( sanitize_text_field( $_GET['filter'] ) ) : $this->max_year;

		remove_action( 'welcome_panel', 'wp_welcome_panel' );
		add_action( 'welcome_panel', [ $this, 'render_widget' ] );
		add_action( 'init', [ $this, 'maybe_export_report' ], 20 );
	}

	public function maybe_export_report() {
		if ( isset( $_GET['export'] ) === false ) {
			return false;
		}

		switch ( sanitize_text_field( $_GET['export'] ) ) {
			case 'golfers':
				$this->force_golfers_report();
				break;
			case 'donations':
				$this->force_donations_report();
				break;
		}
	}

	public function force_donations_report() {
		$this->accounts_from_orders( $this->selected_year, -1 );

		$this->apply_order();


		# Generate CSV data from array
		$fh = fopen( 'php://temp', 'rw' ); # don't create a file, attempt to use memory instead

		# write out the headers
		$headers = [
			'golfer_name',
			'date',
			'donor_name',
			'message',
			'amount',
		];
		fputcsv( $fh, $headers );
		foreach ( $this->accounts as $account ) {
			foreach ( $account['event_100_holes_donations_report'] as $donation ) {
				$row = [ 'golfer_name' => $account['name'] ] + $donation;
				fputcsv( $fh, $row );
			}
		}
		rewind( $fh );
		$csv = stream_get_contents( $fh );
		fclose( $fh );

		header( 'Content-Type: text/csv' );
		header( "Content-Disposition: attachment; filename=donations-report-{$this->selected_year}.csv" );
		echo $csv;

		exit;
	}

	public function force_golfers_report() {
		$include_report_user_id = isset( $_GET['dig'] ) ? intval( sanitize_text_field( $_GET['dig'] ) ) : 0;
		$this->accounts_from_orders( $this->selected_year, $include_report_user_id );

		$this->apply_order();

		$accounts = $this->accounts;

		// apply request filters
		if ( isset( $_GET['user_id'] ) && $_GET['user_id'] !== '' ) {
			$scramble_captain_id = intval( sanitize_text_field( $_GET['user_id'] ) );
			$accounts = array_filter( $accounts, function ( $value ) use ( $scramble_captain_id ) {
				return ( $value['user_id'] === $scramble_captain_id || $value['event_scramble_captain_id'] === $scramble_captain_id );
			} );
		}

		if ( isset( $_GET['event_types'] ) ) {
			$event_types = array_intersect( self::ALLOWED_EVENT_TYPES, array_map( 'sanitize_text_field', $_GET['event_types'] ) );

			// do filtering only if 2 arrays are different
			if ( $event_types !== self::ALLOWED_EVENT_TYPES ) {
				$accounts = array_filter( $accounts, function ( $value ) use ( $event_types ) {
					$satisfies = false;
					foreach ( $event_types as $event_type ) {
						if ( $satisfies === false ) {
							$satisfies = ( $value[ $event_type ] === true );
						}
					}

					return $satisfies;
				} );
			}
		}

		# Generate CSV data from array
		$fh = fopen( 'php://temp', 'rw' ); # don't create a file, attempt to use memory instead

		# write out the headers
		$headers = [
			'name',
			'tshirt_size',
			'hat_size',

			'contact_email',
			'contact_phone',

			'event_100_holes',
			'event_100_holes_paid',
			'event_100_holes_money_raised',

			'event_scramble',
			'event_scramble_paid',
			'event_scramble_captain',
			'event_scramble_captain_name',

			'event_party',
			'event_party_tickets_quantity',
		];
		fputcsv( $fh, $headers );

		# write out the data
		foreach ( $accounts as $row ) {

			// do some data pre-processing here.
			if ( intval( $row['event_100_holes_fee'] ) === -1 ) {
				$row['event_100_holes_fee']          = null;
				$row['event_100_holes_paid']         = null;
				$row['event_100_holes_money_raised'] = null;
			} else {
				if ( $row['event_100_holes_paid'] === true ) {
					$row['event_100_holes_paid'] = $row['event_100_holes_fee'];
				} else {
					$row['event_100_holes_paid'] = 0;
				}
			}

			if ( intval( $row['event_scramble_fee'] ) === -1 ) {
				$row['event_scramble_paid'] = null;
			} else {
				if ( $row['event_scramble_paid'] === true ) {
					$row['event_scramble_paid'] = $row['event_scramble_fee'];
				} else {
					$row['event_scramble_paid'] = 0;
				}
			}

			if ( intval( $row['event_party_tickets_quantity'] ) === -1 ) {
				$row['event_party_tickets_quantity'] = null;
			}


			$row = array_filter( $row, function ( $key ) use ( $headers ) {
				return ( in_array( $key, $headers ) );
			}, ARRAY_FILTER_USE_KEY );

			fputcsv( $fh, $row );
		}
		rewind( $fh );
		$csv = stream_get_contents( $fh );
		fclose( $fh );

		header( 'Content-Type: text/csv' );
		header( "Content-Disposition: attachment; filename=golfers-report-{$this->selected_year}.csv" );
		echo $csv;

		exit;
	}

	public function render_widget() {
		$include_report_user_id = isset( $_GET['dig'] ) ? intval( sanitize_text_field( $_GET['dig'] ) ) : 0;
		$this->accounts_from_orders( $this->selected_year, $include_report_user_id );


		// Collect scramble captains (before filtering)
		$scramble_captains = [];
		foreach ( $this->accounts as $account ) {
			if ( $account['event_scramble'] && $account['event_scramble_captain'] ) {
				$scramble_captains[ $account['user_id'] ] = $account['name'];
			}
		}
		asort( $scramble_captains, SORT_NATURAL | SORT_FLAG_CASE );


		// Calculate stats
		$donation_total = 0;
		$count_event_100_holes = 0;
		$count_goal_not_meet = 0;
		$count_scramble = 0;
		$count_missing_scramble_captain = 0;
		$count_missing_shirt_hat_size = 0;
		foreach ( $this->accounts as $account ) {

			if ( $account['event_100_holes'] ) {
				$count_event_100_holes++;

				$donation_total += $account['event_100_holes_money_raised'];

				if ( ! $account['event_100_holes_donation_goal_met'] ) {
					$count_goal_not_meet++;
				}
			}

			if ( $account['event_scramble'] ) {
				$count_scramble++;
				if ( empty( $account['name'] ) || $account['name'] === '—' ) {
					$count_missing_scramble_captain++;
				}
			}

			if ( $account['event_100_holes'] || $account['event_scramble'] ) {
				if ( empty( $account['tshirt_size'] ) || empty( $account['hat_size'] ) ) {
					$count_missing_shirt_hat_size++;
				}
			}

		}


		// Apply filters
		if ( isset( $_GET['user_id'] ) && $_GET['user_id'] !== '' ) {
			$scramble_captain_id = intval( sanitize_text_field( $_GET['user_id'] ) );
			$this->accounts = array_filter( $this->accounts, function ( $value ) use ( $scramble_captain_id ) {
				return ( $value['user_id'] === $scramble_captain_id || $value['event_scramble_captain_id'] === $scramble_captain_id );
			} );
		} else {
			$scramble_captain_id = '';
		}

		if ( isset( $_GET['event_types'] ) ) {
			$event_types = array_intersect( self::ALLOWED_EVENT_TYPES, array_map( 'sanitize_text_field', $_GET['event_types'] ) );

			// Filter only if 2 arrays are different
			if ( $event_types !== self::ALLOWED_EVENT_TYPES ) {
				$this->accounts = array_filter( $this->accounts, function ( $value ) use ( $event_types ) {
					$satisfies = false;
					foreach ( $event_types as $event_type ) {
						if ( $satisfies === false ) {
							$satisfies = ( $value[ $event_type ] === true );
						}
					}

					return $satisfies;
				} );
			}
		} else {
			$event_types = self::ALLOWED_EVENT_TYPES;
		}

		// Missing data filter
		if ( isset( $_GET['filter-missing'] ) ) {
			$filter_missing = sanitize_text_field( $_GET['filter-missing'] );
			switch ( $filter_missing ) {
				case '100-golfers':
					$filter_callable = function ( $account ) {
						return ( $account['event_100_holes'] && ! $account['event_100_holes_donation_goal_met'] );
					};
					break;
				case 'hat-tshirt':
					$filter_callable = function ( $account ) {
						return ( $account['event_100_holes'] || $account['event_scramble'] ) && ( empty( $account['tshirt_size'] ) || empty( $account['hat_size'] ) );
					};
					break;
				case 'scramble-captain':
					$filter_callable = function ( $account ) {
						return ( $account['event_scramble'] && ( empty( $account['name'] ) || $account['name'] === '—' ) );
					};
					break;
			}

			$this->accounts = array_filter( $this->accounts,  $filter_callable );
		}

		// Sort (after filtering)
		$this->apply_order();


		echo wc_get_template( 'admin/dashboard/report.php', [
			'min_year'                       => self::MIN_YEAR,
			'max_year'                       => $this->max_year,
			'selected_year'                  => $this->selected_year,
			'accounts'                       => $this->accounts,
			'scramble_captains'              => $scramble_captains,
			'scramble_captain_id'            => $scramble_captain_id,
			'event_types'                    => $event_types,
			'donation_total'                 => $donation_total,
			'count_goal_not_meet'            => "{$count_goal_not_meet} <span class='light'>of {$count_event_100_holes}</span>",
			'count_missing_scramble_captain' => "{$count_missing_scramble_captain} <span class='light'>of {$count_scramble}</span>",
			'count_missing_shirt_hat_size'   => $count_missing_shirt_hat_size,
		], '', B4B_PLUGIN_PATH . '/templates/' );
	}

	private function accounts_from_orders( $year, $include_report_user_id = 0 ) {
		// TODO: speed: 1s
//$this->time1 = microtime(true);
		// Fetch orders for year
		$all_orders_in_year_query = new WC_Order_Query( [
			'status'         => [ 'on-hold', 'processing', 'completed' ],
			'date_created'   => sprintf( '%d-01-01...%d-12-31', $year, $year ),
			'type'           => [ 'shop_order' ],
			'posts_per_page' => -1,

			// 'cache_results'          => true,
			// 'update_post_meta_cache' => true,
			// 'update_post_term_cache' => true,
		] );
		$all_orders_in_year       = $all_orders_in_year_query->get_orders();

//global $wpdb;

//$all_orders_in_year = $wpdb->get_results($wpdb->prepare("
//	SELECT *
//	FROM {$wpdb->prefix}posts
//	WHERE
//		post_type = 'shop_order'
//		AND post_status IN ('wc-on-hold', 'wc-processing', 'wc-completed')
//		AND (post_date >= '{$year}-01-01 00:00:00' AND post_date <= '{$year}-12-31 23:59:59')
//	ORDER BY post_date;
//"));
//print_r($all_orders_in_year);
//SELECT wp_egk73m5rf6_posts.*
//FROM wp_egk73m5rf6_posts
//WHERE 1=1
//AND ( ( wp_egk73m5rf6_posts.post_date >= '2019-01-01 00:00:00'
//AND wp_egk73m5rf6_posts.post_date <= '2019-12-31 23:59:59' ) )
//AND wp_egk73m5rf6_posts.post_type = 'shop_order'
//AND ((wp_egk73m5rf6_posts.post_status = 'wc-processing'
//OR wp_egk73m5rf6_posts.post_status = 'wc-on-hold'
//OR wp_egk73m5rf6_posts.post_status = 'wc-completed'))
//ORDER BY wp_egk73m5rf6_posts.post_date DESC


//$this->time2 = microtime(true);
//echo "<br>\n ET(s): ", round($this->time2 - $this->time1, 3), '; ========= ; (', round($this->time1, 3), ' - ', round($this->time2, 3), ')'; // TODO: remove

//// Manually precache woocommerce_order_items for all orders
//$order_ids = [];
//foreach ( $all_orders_in_year as $order ) {
//	$order_id = (int) $order->get_id();
//	$order_ids[ $order_id ] = $order_id;
//}
////var_dump( $order_ids );
//
//if ( $order_ids ) {
//global $wpdb;
//
//$order_item_ids = [];
//$items = $wpdb->get_results(
//	$wpdb->prepare( "SELECT order_item_type, order_item_id, order_id, order_item_name FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id IN (" . implode( ',', $order_ids ) . ") ORDER BY order_id, order_item_id;", $order->get_id() )
//);
//$old_id = false;
//$items_segment = [];
//foreach ( $items as $item ) {
//	$order_item_ids[] = $item->order_item_id;
//	wp_cache_set( 'item-' . $item->order_item_id, $item, 'order-items' );
//
//	$items_segment[] = $item;
//
//	if ( $old_id !== $item->order_id ) {
//		if ( $old_id ) {
////			print_r($items_segment);
//			wp_cache_set( 'order-items-' . $item->order_id, $items_segment, 'orders' );
//			$items_segment = [];
//		}
//
//		$old_id = $item->order_id;
//	}
//}


//$raw_meta_data = $wpdb->get_results(
//	$wpdb->prepare(
//		"SELECT order_item_id, meta_id, meta_key, meta_value
//		FROM {$wpdb->prefix}woocommerce_order_itemmeta
//		WHERE order_item_id IN (" . implode( ',', $order_item_ids ) . ")
//		ORDER BY order_item_id, meta_id"
//	)
//);
//$old_id = false;
//$meta_segment = [];
//$cache_group = 'orders';
//foreach ( $raw_meta_data as $meta_row ) {
//	$meta_segment[] = $meta_row;
////var_dump($old_id);
////var_dump($meta_row->order_item_id);
////var_dump($old_id !== $meta_row->order_item_id);
//
//	if ( $old_id !== $meta_row->order_item_id ) {
//		if ( $old_id ) {
//			$cache_key = WC_Cache_Helper::get_cache_prefix( $cache_group ) . WC_Cache_Helper::get_cache_prefix( 'object_' . $meta_row->order_item_id ) . 'object_meta_' . $meta_row->order_item_id;
////print_r($cache_key);
////print_r($meta_segment);
//echo "WIDGET{$meta_row->order_item_id}---";
////die;
//			wp_cache_set( $cache_key, $meta_segment, $cache_group );
//
//			$meta_segment = [];
//		}
//
//		$old_id = $meta_row->order_item_id;
//	}
//}
////die;
////print_r($meta_segment);
//
//
//}

//$this->time1 = microtime(true);
		// TODO: speed: 2.2s
		$orders = [];
		$order_atts = [];
		$user_atts = [];
		$user_ids = [];
		$donation_goals = [];
		foreach ( $all_orders_in_year as $order ) {
			$order_id = (int) $order->get_id();
			$customer_id = (int) $order->get_customer_id();

			// Calculate donations (without type check)
			$donated_golfer_id = false;
			if ( $order->meta_exists( '_b4b_donated_golfer_id' ) ) {
				$donated_golfer_id = (int) $order->get_meta( '_b4b_donated_golfer_id', true );

				// Init
				if ( ! isset( $user_atts[ $donated_golfer_id ]['event_100_holes_money_raised'] ) ) {
					$user_atts[ $donated_golfer_id ]['event_100_holes_money_raised'] = 0;
					$user_atts[ $donated_golfer_id ]['event_100_holes_donations_report'] = [];
				}

				$donation_total = floatval( $order->get_total() );
				$user_atts[ $donated_golfer_id ]['event_100_holes_money_raised'] += $donation_total;

				if ( $donated_golfer_id === $include_report_user_id || $include_report_user_id === -1 ) {
					$user_atts[ $donated_golfer_id ]['event_100_holes_donations_report'][] = [
						'date'       => $order->get_date_paid(),
						'donor_name' => sprintf( '%s %s', $order->get_billing_first_name(), $order->get_billing_last_name() ),
						'message'    => $order->get_customer_note(),
						'amount'     => $donation_total,
						'order_link' => $order->get_edit_order_url(),
					];
				}
			}


			foreach ( $order->get_items() as $order_item ) {
				$product = $order_item->get_product();

				// Check if order is eligible
				$type = $product->get_type();
				if ( ! in_array( $type, self::ALLOWED_EVENT_TYPES ) ) {
					continue;
				}

				$order_atts[ $order_id ][ $type ] = true;

				if ( $type === 'event_100_holes' ) {
					$product_id = $product->get_id();

					// Get donation goal
					if ( ! isset( $donation_goals[ $product_id ] ) ) {
						$donation_goal_product_query = new WC_Product_Query( [
							'status'            => 'publish',
							'type'              => 'donation',
							'donation_event_id' => $product_id,
							'posts_per_page'    => -1,
							'limit'             => 1,
						] );
						$donation_goal_product = $donation_goal_product_query->get_products();

						$donation_goal = 0;
						if ( empty( $donation_goal_product ) === false ) {
							$donation_goal_product = current( $donation_goal_product );  // take first
							$donation_goal = $donation_goal_product->get_meta( '_b4b_donation_goal', true );
						}

						$donation_goals[ $product_id ] = $donation_goal;
					}
					$user_atts[ $customer_id ]['event_100_holes_donation_goal'] = $donation_goals[ $product_id ];
				} else if ( $type === 'event_party' ) {
					if ( ! isset ( $user_atts[ $customer_id ]['event_party_tickets_quantity'] ) ) {
						$user_atts[ $customer_id ]['event_party_tickets_quantity'] = 0;
					}
					$user_atts[ $customer_id ]['event_party_tickets_quantity'] += $order_item->get_quantity();
				}
			}

			if ( ! empty( $order_atts[ $order_id ] ) ) {
				$orders[] = $order;

				if ( ! isset( $user_ids[ $customer_id ] ) ) {
					$user_ids[ $customer_id ] = $customer_id;
				}
			}
		}
		unset( $all_orders_in_year );
//$this->time2 = microtime(true);
//echo "<br>\n ET(s): ", round($this->time2 - $this->time1, 3), '; ========= ; (', round($this->time1, 3), ' - ', round($this->time2, 3), ')'; // TODO: remove

		// Skip if there are no users
		if ( empty( $user_ids ) ) {
			return;
		}

//$this->time1 = microtime(true);
		// Fetch users (+ meta data)
		$users = get_users( [
			'include' => $user_ids,
			'fields'  => 'all_with_meta',
		] );

		foreach ( $users as $user ) {
			$user_id = (int) $user->ID;
			$this->accounts[ $user_id ] = [
				'user_id'       => $user_id,
				'user_edit_url' => get_edit_user_link( $user_id ),
				'name'          => sprintf( '%s %s', $user->first_name, $user->last_name ),
				'tshirt_size'   => get_user_meta( $user_id, 'b4b_tshirt_size', true ),
				'hat_size'      => get_user_meta( $user_id, 'b4b_hat_size', true ),
				'contact_email' => $user->user_email,
				'contact_phone' => get_user_meta( $user_id, 'billing_phone', true ),

				'event_100_holes'              => false,
				'event_100_holes_fee'          => -1,
				'event_100_holes_paid'         => false,
				'event_100_holes_money_raised' => 0,

				'event_scramble'               => false,
				'event_scramble_fee'           => -1,
				'event_scramble_paid'          => false,
				'event_scramble_captain'       => false,
				'event_scramble_captain_id'    => null,
				'event_scramble_captain_name'  => null,
				'event_scramble_order_url'     => null,

				'event_party'                  => false,
				'event_party_tickets_quantity' => -1,
			];
		}
		unset( $users );

		foreach ( $orders as $order ) {
			$order_id = (int) $order->get_id();
			if ( isset( $order_atts[ $order_id ] ) ) {
				$customer_id = (int) $order->get_customer_id();

				if ( ! $this->accounts[ $customer_id ]['event_100_holes'] && isset( $order_atts[ $order_id ]['event_100_holes'] ) ) {
					$this->accounts[ $customer_id ]['event_100_holes']                   = true;
					$this->accounts[ $customer_id ]['event_100_holes_paid']              = $order->is_paid();
					$this->accounts[ $customer_id ]['event_100_holes_fee']               = $order->get_total();
					$this->accounts[ $customer_id ]['event_100_holes_order_url']         = $order->get_edit_order_url();
					$this->accounts[ $customer_id ]['event_100_holes_donation_goal']     = isset( $user_atts[ $customer_id ]['event_100_holes_donation_goal'] ) ? $user_atts[ $customer_id ]['event_100_holes_donation_goal'] : 0;
					$this->accounts[ $customer_id ]['event_100_holes_money_raised']      = isset( $user_atts[ $customer_id ]['event_100_holes_money_raised'] ) ? $user_atts[ $customer_id ]['event_100_holes_money_raised'] : 0;
					$this->accounts[ $customer_id ]['event_100_holes_donation_goal_met'] = ( $this->accounts[ $customer_id ]['event_100_holes_money_raised'] > $this->accounts[ $customer_id ]['event_100_holes_donation_goal'] );
					if ( ! empty( $user_atts[ $customer_id ]['event_100_holes_donations_report'] ) ) {
						$this->accounts[ $customer_id ]['event_100_holes_donations_report'] = $user_atts[ $customer_id ]['event_100_holes_donations_report'];
					}
				}

				if ( isset( $order_atts[ $order_id ]['event_scramble'] ) ) {
					if ( ! $this->accounts[ $customer_id ]['event_scramble'] ) {
						$this->accounts[ $customer_id ]['event_scramble']                = true;
						$this->accounts[ $customer_id ]['event_scramble_captain']        = true;
						$this->accounts[ $customer_id ]['event_scramble_captain_id']     = false;
						$this->accounts[ $customer_id ]['event_scramble_paid']           = $order->is_paid();
						$this->accounts[ $customer_id ]['event_scramble_fee']            = $order->get_total();
						$this->accounts[ $customer_id ]['event_scramble_order_url']      = $order->get_edit_order_url();
						$this->accounts[ $customer_id ]['event_scramble_captain_name']   = $this->accounts[ $customer_id ]['name'];
					}

					$this->accounts_from_scramble( $order );
				}

				if ( ! $this->accounts[ $customer_id ]['event_party'] && isset( $order_atts[ $order_id ]['event_party'] ) ) {
					$this->accounts[ $customer_id ]['event_party']                  = true;
					$this->accounts[ $customer_id ]['event_party_tickets_quantity'] = $user_atts[ $customer_id ]['event_party_tickets_quantity'];
				}
			}
		}
//$this->time2 = microtime(true);
//echo "<br>\n ET(s): ", round($this->time2 - $this->time1, 3), '; ========= ; (', round($this->time1, 3), ' - ', round($this->time2, 3), ')'; // TODO: remove
	}

	private function accounts_from_scramble( $order ) {
		$captain_user_id       = (int) $order->get_customer_id();
		$scramble_team_members = b4b_scramble_team_meta_get( $order->get_id() );

		foreach ( $scramble_team_members as $team_member_id => $team_member_data ) {
			$user_key = $captain_user_id . '.' . $team_member_id;
			if ( isset( $this->accounts[ $user_key ] ) ) {
				continue;
			}

			$this->accounts[ $user_key ] = [
				'user_id'       => 0,
				'user_edit_url' => $this->accounts[ $captain_user_id ]['event_scramble_order_url'],
				'name'          => ( empty( $team_member_data['name'] ) === false ) ? $team_member_data['name'] : '—',
				'tshirt_size'   => $team_member_data['tshirt_size'],
				'hat_size'      => $team_member_data['hat_size'],
				'contact_email' => $team_member_data['email'] ?: $this->accounts[ $captain_user_id ]['contact_email'],
				'contact_phone' => $team_member_data['phone'] ?: $this->accounts[ $captain_user_id ]['contact_phone'],

				'event_100_holes'              => false,
				'event_100_holes_fee'          => -1,
				'event_100_holes_paid'         => false,
				'event_100_holes_money_raised' => -1,

				'event_scramble'               => true,
				'event_scramble_fee'           => -1,
				'event_scramble_paid'          => false,
				'event_scramble_captain'       => false,
				'event_scramble_captain_id'    => $captain_user_id,
				'event_scramble_captain_name'  => $this->accounts[ $captain_user_id ]['name'],
				'event_scramble_order_url'     => '#account-' . $captain_user_id,

				'event_party'                  => false,
				'event_party_tickets_quantity' => -1,
			];
		}
	}

	private function apply_order() {
		$order_by = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'name';
		$order = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'asc';
		$order = ( $order === 'asc' ) ? SORT_ASC : SORT_DESC;

		$sort_col = [];
		foreach ( $this->accounts as $key => $row ) {
			$value = $row[ $order_by ];

			$is_empty = false;
			if ( $order_by === 'name' ) {
				if ( empty( $row['name'] ) || $row['name'] === '—' ) {
					$is_empty = true;
				}
			} else if ( $order_by === 'event_100_holes_fee' ) {
				if ( ! $row['event_100_holes'] ) {
					$is_empty = true;
				} else if ( ! $row['event_100_holes_paid'] ) {
					$value = '0';
				} else if ( empty( $row[ $order_by ] ) || $row[ $order_by ] === -1 ) {
					$is_empty = true;
				}
			} else if ( $order_by === 'event_100_holes_money_raised' ) {
				if ( ! $row['event_100_holes'] ) {
					$is_empty = true;
				}
			} else if ( $order_by === 'event_scramble_captain_name' ) {
				if ( ! $row['event_scramble'] ) {
					$is_empty = true;
				}
			} else {
				if ( empty( $row[ $order_by ] ) || $row[ $order_by ] === -1 ) {
					$is_empty = true;
				}
			}

			if ( $is_empty ) {
				$value = ( $order === SORT_ASC ) ? '———' : '&&&';
			}

			$sort_col[ $key ] = strtolower( trim( $value ) );
		}
		array_multisort( $sort_col, $order, $this->accounts );
	}

}
