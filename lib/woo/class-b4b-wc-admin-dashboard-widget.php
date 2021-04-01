<?php


namespace B4B_Theme_Support\Lib\Woo;

use WC_Order_Query;
use WC_Product_Query;
use WP_User;
use WC_Order;

class B4B_WC_Admin_Dashboard_Widget {

	const MIN_YEAR = 2016;
	/**
	 * @var $orders array All orders in selected year
	 */
	protected $orders;

	/**
	 * @var $accounts array All accounts associated (customer_id) with orders in selected year
	 */
	protected $accounts;

	public function __construct() {
		remove_action( "welcome_panel", "wp_welcome_panel" );
		add_action( "welcome_panel", [ $this, "render_widget" ] );
		add_action( "init", [ $this, "maybe_export_report" ], 20 );
	}

	public function maybe_export_report() {
		if ( is_admin() === false || isset( $_REQUEST["export"] ) === false ) {
			return false;
		}

		switch ( sanitize_text_field( $_REQUEST["export"] ) ) {
			case "golfers":
				$this->force_golfers_report();
				break;
			case "donations":
				$this->force_donations_report();
				break;
		}
	}

	public function force_donations_report() {
		if ( is_admin() === false || isset( $_REQUEST["export"] ) === false ) {
			return false;
		}

		$export = sanitize_text_field( $_REQUEST["export"] );
		if ( $export !== 'donations' ) {
			return false;
		}

		$min_year      = self::MIN_YEAR;
		$max_year      = intval( date( "Y" ) );
		$selected_year = intval( sanitize_text_field( $_REQUEST["filter"] ) );
		if ( empty( $selected_year ) === true ) {
			$selected_year = $max_year;
		}

		$this->orders_for_year( $selected_year );
		$this->accounts_from_orders();

		$this->gather_money_raised_for_accounts( $selected_year, - 1 );

		$this->apply_order();

		$accounts = $this->accounts;


		# Generate CSV data from array
		$fh = fopen( 'php://temp', 'rw' ); # don't create a file, attempt
		# to use memory instead

		# write out the headers
		$headers = [
			"golfer_name",
			"date",
			"donor_name",
			"message",
			"amount",
		];
		fputcsv( $fh, $headers );
		foreach ( $accounts as $account ) {
			foreach ( $account["event_100_holes_donations_report"] as $donation ) {
				$row = [ "golfer_name" => $account["name"] ] + $donation;
				fputcsv( $fh, $row );
			}
		}
		rewind( $fh );
		$csv = stream_get_contents( $fh );
		fclose( $fh );

		header( "Content-Type: text/csv" );
		header( "Content-Disposition: attachment; filename=donations-report-$selected_year.csv" );
		echo $csv;

		exit;
	}

	public function force_golfers_report() {
		$min_year      = self::MIN_YEAR;
		$max_year      = intval( date( "Y" ) );
		$selected_year = intval( sanitize_text_field( $_REQUEST["filter"] ) );
		if ( empty( $selected_year ) === true ) {
			$selected_year = $max_year;
		}

		$this->orders_for_year( $selected_year );
		$this->accounts_from_orders();
		$this->tickets_quantity_for_orders( $selected_year );

		$include_report_user_id = intval( sanitize_text_field( $_REQUEST["dig"] ) );
		$this->gather_money_raised_for_accounts( $selected_year, $include_report_user_id );

		$this->apply_order();

		$accounts = $this->accounts;
		//apply request filters
		$scramble_captain_id = 0;
		if ( isset( $_REQUEST["user_id"] ) ) {
			$scramble_captain_id = intval( sanitize_text_field( $_REQUEST["user_id"] ) );
		}

		if ( $scramble_captain_id > 0 ) {
			$accounts = array_filter( $accounts, function ( $value ) use ( $scramble_captain_id ) {
				return ( $value["user_id"] === $scramble_captain_id
				         || $value["event_scramble_captain_id"] === $scramble_captain_id );
			} );
		}

		$allowed_event_types = [
			"event_scramble",
			"event_100_holes",
			"event_party",
		];
		if ( isset( $_REQUEST["event_types"] ) ) {
			$event_types = array_intersect( $allowed_event_types, array_map( "sanitize_text_field", $_REQUEST["event_types"] ) );
		} else {
			$event_types = $allowed_event_types;
		}
		//do filtering only if 2 arrays are different
		if ( $event_types !== $allowed_event_types ) {
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

		# Generate CSV data from array
		$fh = fopen( 'php://temp', 'rw' ); # don't create a file, attempt
		# to use memory instead

		# write out the headers
		$headers = [
			"name",
			"tshirt_size",
			"hat_size",

			"contact_email",
			"contact_phone",

			"event_100_holes",
			"event_100_holes_paid",
			"event_100_holes_money_raised",

			"event_scramble",
			"event_scramble_paid",
			"event_scramble_captain",
			"event_scramble_captain_name",

			"event_party",
			"event_party_tickets_quantity",
		];
		fputcsv( $fh, $headers );

		# write out the data
		foreach ( $accounts as $row ) {

			// do some data pre-processing here.
			if ( intval( $row["event_100_holes_fee"] ) === - 1 ) {
				$row["event_100_holes_fee"]          = null;
				$row["event_100_holes_paid"]         = null;
				$row["event_100_holes_money_raised"] = null;
			} else {
				if ( $row["event_100_holes_paid"] === true ) {
					$row["event_100_holes_paid"] = $row["event_100_holes_fee"];
				} else {
					$row["event_100_holes_paid"] = 0;
				}
			}

			if ( intval( $row["event_scramble_fee"] ) === - 1 ) {
				$row["event_scramble_paid"] = null;
			} else {
				if ( $row["event_scramble_paid"] === true ) {
					$row["event_scramble_paid"] = $row["event_scramble_fee"];
				} else {
					$row["event_scramble_paid"] = 0;
				}
			}

			if ( intval( $row["event_party_tickets_quantity"] ) === - 1 ) {
				$row["event_party_tickets_quantity"] = null;
			}


			$row = array_filter( $row, function ( $key ) use ( $headers ) {

				return ( in_array( $key, $headers ) );
			}, ARRAY_FILTER_USE_KEY );

			fputcsv( $fh, $row );
		}
		rewind( $fh );
		$csv = stream_get_contents( $fh );
		fclose( $fh );

		header( "Content-Type: text/csv" );
		header( "Content-Disposition: attachment; filename=golfers-report-$selected_year.csv" );
		echo $csv;

		exit;

	}

	public function render_widget() {

		$min_year      = self::MIN_YEAR;
		$max_year      = intval( date( "Y" ) );
		$selected_year = intval( sanitize_text_field( $_REQUEST["filter"] ) );
		if ( empty( $selected_year ) === true ) {
			$selected_year = $max_year;
		}

		$this->orders_for_year( $selected_year );
		$this->accounts_from_orders();
		$this->tickets_quantity_for_orders( $selected_year );

		$include_report_user_id = intval( sanitize_text_field( $_REQUEST["dig"] ) );
		$this->gather_money_raised_for_accounts( $selected_year, $include_report_user_id );

		$accounts_scramble = array_filter( $this->accounts, function ( $value ) {
			return ( $value["event_scramble"] === true && $value["event_scramble_captain"] === true );
		} );

		$this->apply_order();
		$accounts = $this->accounts;
		//apply request filters
		$scramble_captain_id = 0;
		if ( isset( $_REQUEST["user_id"] ) ) {
			$scramble_captain_id = intval( sanitize_text_field( $_REQUEST["user_id"] ) );
		}

		if ( $scramble_captain_id > 0 ) {
			$accounts = array_filter( $accounts, function ( $value ) use ( $scramble_captain_id ) {
				return ( $value["user_id"] === $scramble_captain_id
				         || $value["event_scramble_captain_id"] === $scramble_captain_id );
			} );
		}

		$allowed_event_types = [
			"event_scramble",
			"event_100_holes",
			"event_party",
		];
		if ( isset( $_REQUEST["event_types"] ) ) {
			$event_types = array_intersect( $allowed_event_types, array_map( "sanitize_text_field", $_REQUEST["event_types"] ) );
		} else {
			$event_types = $allowed_event_types;
		}
		//do filtering only if 2 arrays are different
		if ( $event_types !== $allowed_event_types ) {
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

		//missing data filter
		if ( isset( $_REQUEST["missing_data"] ) && sanitize_text_field( $_REQUEST["missing_data"] ) === "only" ) {
			$accounts = array_filter( $accounts, function ( $account ) {
				return (
					empty( $account["tshirt_size"] ) === true ||
					empty( $account["hat_size"] ) === true ||
					empty( $account["name"] ) === true
				);
			} );
		}

		//account in dropdown are always sorted like this
		b4b_array_sort_by_column( $accounts_scramble, "name" );

		echo wc_get_template( "admin/dashboard/report.php", [
			"min_year"            => $min_year,
			"max_year"            => $max_year,
			"selected_year"       => $selected_year,
			"accounts"            => $accounts,
			"total_accounts"      => count( $this->accounts ),
			"accounts_scramble"   => $accounts_scramble,
			"scramble_captain_id" => $scramble_captain_id,
			"event_types"         => $event_types,
		], "", B4B_PLUGIN_PATH . "/templates/" );
	}

	private function orders_for_year( $year ) {
		$all_orders_in_year_query = new WC_Order_Query( [
			"status"         => [ "on-hold", "processing", "completed" ],
			"date_created"   => sprintf( "%d-01-01...%d-12-31", $year, $year ),
			"type"           => [ "shop_order" ],
			"posts_per_page" => - 1,
		] );
		$all_orders_in_year       = $all_orders_in_year_query->get_orders();

		foreach ( $all_orders_in_year as $order ) {
			if ( b4b_have_event_in_order( $order->get_id() ) === true ) {
				$this->orders[] = $order;
			}
		}
	}

	private function accounts_from_orders() {
		foreach ( $this->orders as $order ) {
			$user = new WP_User( $order->get_customer_id() );

			if ( isset( $this->accounts[ $user->ID ] ) === false ) {
				$this->accounts[ $user->ID ] = [
					"user_id"       => $user->ID,
					"user_edit_url" => get_edit_user_link( $user->ID ),
					"name"          => sprintf( "%s %s", $user->first_name, $user->last_name ),
					"tshirt_size"   => get_user_meta( $user->ID, "b4b_tshirt_size", true ),
					"hat_size"      => get_user_meta( $user->ID, "b4b_hat_size", true ),
					"contact_email" => $user->user_email,
					"contact_phone" => get_user_meta( $user->ID, "billing_phone", true ),

					"event_100_holes"              => false,
					"event_100_holes_fee"          => - 1,
					"event_100_holes_paid"         => false,
					"event_100_holes_money_raised" => - 1,

					"event_scramble"              => false,
					"event_scramble_fee"          => - 1,
					"event_scramble_paid"         => false,
					"event_scramble_captain"      => false,
					"event_scramble_captain_id"   => null,
					"event_scramble_captain_name" => null,
					"event_scramble_order_url"    => null,

					"event_party"                  => false,
					"event_party_tickets_quantity" => - 1,
				];
			}

			if ( b4b_have_event_in_order( $order, "event_100_holes" ) ) {
				$this->accounts[ $user->ID ] = array_merge( $this->accounts[ $user->ID ],
					[
						"event_100_holes"               => true,
						"event_100_holes_paid"          => $order->is_paid(),
						"event_100_holes_fee"           => $order->get_total(),
						"event_100_holes_order_url"     => $order->get_edit_order_url(),
						"event_100_holes_donation_goal" => $this->get_donation_goal_from_order( $order ),
						"event_100_holes_money_raised"  => 0,
					] );
			}

			if ( b4b_have_event_in_order( $order, "event_scramble" ) ) {
				$this->accounts[ $user->ID ] = array_merge( $this->accounts[ $user->ID ],
					[
						"event_scramble"              => true,
						"event_scramble_captain"      => true,
						"event_scramble_captain_id"   => false,
						"event_scramble_paid"         => $order->is_paid(),
						"event_scramble_fee"          => $order->get_total(),
						"event_scramble_order_url"    => $order->get_edit_order_url(),
						"event_scramble_captain_name" => $this->accounts[ $user->ID ]["name"],
					] );

				$this->accounts_from_scramble( $order );
			}

			if ( b4b_have_event_in_order( $order, "event_party" ) ) {
				$this->accounts[ $user->ID ] = array_merge( $this->accounts[ $user->ID ],
					[
						"event_party"                  => true,
						"event_party_tickets_quantity" => 0,
					] );
			}
		}
	}

	private function accounts_from_scramble( $order ) {
		$captain_user          = new WP_User( $order->get_customer_id() );
		$scramble_team_members = get_post_meta( $order->get_id(), "_b4b_scramble_team_members", true );
		if ( empty( $scramble_team_members ) === true ) {
			$scramble_team_members = b4b_scramble_team_meta_blueprint();
		}

		foreach ( $scramble_team_members as $team_member_id => $team_member_data ) {
			if ( isset( $this->accounts[ $captain_user->ID . "." . $team_member_id ] ) === false ) {
				$this->accounts[ $captain_user->ID . "." . $team_member_id ] = [
					"user_id"       => 0,
					"user_edit_url" => "",
					"name"          => ( empty( $team_member_data["name"] ) === false ) ? $team_member_data["name"] : '&mdash;',
					"tshirt_size"   => $team_member_data["tshirt_size"],
					"hat_size"      => $team_member_data["hat_size"],
					"contact_email" => $captain_user->user_email,
					"contact_phone" => get_user_meta( $captain_user->ID, "billing_phone", true ),

					"event_100_holes"              => false,
					"event_100_holes_fee"          => - 1,
					"event_100_holes_paid"         => false,
					"event_100_holes_money_raised" => - 1,

					"event_scramble"              => true,
					"event_scramble_fee"          => - 1,
					"event_scramble_paid"         => false,
					"event_scramble_captain"      => false,
					"event_scramble_captain_id"   => $captain_user->ID,
					"event_scramble_captain_name" => $this->accounts[ $captain_user->ID ]["name"],
					"event_scramble_order_url"    => "#account-" . $captain_user->ID,

					"event_party"                  => false,
					"event_party_tickets_quantity" => - 1,
				];
			}
		}
	}

	private function get_donation_goal_from_order( WC_Order $order ) {

		$goal      = 0;
		$donations = [];

		//get donation product from order
		foreach ( $order->get_items() as $item ) {
			$product = wc_get_product( $item->get_data()["product_id"] );
			// find donation for this product
			$donations_query = new WC_Product_Query( [
				"status"            => "publish",
				"type"              => "donation",
				"donation_event_id" => $product->get_id(),
				"posts_per_page"    => - 1,
			] );
			$donations       = $donations_query->get_products();
		}

		if ( empty( $donations ) === false ) {
			$donation = current( $donations ); //take first
			$goal     = get_post_meta( $donation->get_id(), "_b4b_donation_goal", true );
		}

		return $goal;
	}

	private function gather_money_raised_for_accounts( $year, $include_report_user_id = 0 ) {

		foreach ( $this->accounts as $account ) {
			if ( $account["user_id"] > 0 && $account["event_100_holes"] === true ) {
				$donations_query     = new WC_Order_Query( [
					"status"            => [
						"completed",
						"processing",
						"on-hold",
					],
					"date_paid"         => sprintf( "%d-01-01...%d-12-31", $year, $year ),
					"donated_golfer_id" => $account["user_id"],
					"posts_per_page"    => - 1,
				] );
				$donations           = $donations_query->get_orders();
				$donations_collected = 0;
				$donations_list      = [];
				foreach ( $donations as $donation ) {
					/**
					 * @var $donation WC_Order
					 */
					if ( $account["user_id"] === $include_report_user_id || $include_report_user_id === - 1 ) {
						$donations_list[] = [
							"date"       => $donation->get_date_paid(),
							"donor_name" => sprintf( "%s %s", $donation->get_billing_first_name(), $donation->get_billing_last_name() ),
							"message"    => $donation->get_customer_note(),
							"amount"     => floatval( $donation->get_total() ),
							"order_link" => $donation->get_edit_order_url(),
						];
					}

					$donations_collected += floatval( $donation->get_total() );
				}

				if ( $account["user_id"] === $include_report_user_id || $include_report_user_id === - 1 ) {
					$this->accounts[ $account["user_id"] ]["event_100_holes_donations_report"] = $donations_list;
				}

				$this->accounts[ $account["user_id"] ]["event_100_holes_money_raised"]      = $donations_collected;
				$this->accounts[ $account["user_id"] ]["event_100_holes_donation_goal_met"] =
					( $donations_collected > $this->accounts[ $account["user_id"] ]["event_100_holes_donation_goal"] );
			}
		}
	}

	private function tickets_quantity_for_orders( $year ) {
		foreach ( $this->accounts as $account ) {
			if ( $account["event_party"] === true ) {
				// fetch all orders for customer
				$orders_query = new WC_Order_Query( [
					"status"         => [
						"completed",
						"processing",
						"on-hold",
					],
					"posts_per_page" => - 1,
					"customer_id"    => $account["user_id"],
					"date_paid"      => sprintf( "%d-01-01...%d-12-31", $year, $year ),
				] );
				$orders       = $orders_query->get_orders();
				foreach ( $orders as $order ) {
					if ( b4b_have_event_in_order( $order->get_id(), "event_party" ) ) {
						$quantity = 0;
						foreach ( $order->get_items() as $item ) {
							$product = wc_get_product( $item->get_data()["product_id"] );
							if ( b4b_is_product_event( $product, "event_party" ) ) {
								$quantity += $item->get_quantity();
							}
						}

						$this->accounts[ $account["user_id"] ]["event_party_tickets_quantity"] += $quantity;
					}
				}
			}
		}
	}

	private function apply_order() {
		$order_by = sanitize_text_field( $_REQUEST["orderby"] );
		$order    = sanitize_text_field( $_REQUEST["order"] );

		if ( empty( $order_by ) === true ) {
			$order_by = "name";
		}

		if ( $order === "asc" ) {
			$order = SORT_ASC;
		} else {
			$order = SORT_DESC;
		}
		b4b_array_sort_by_column( $this->accounts, $order_by, $order );
	}
}