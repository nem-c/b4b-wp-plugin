<?php

namespace B4B_Theme_Support\Lib\Woo;

use WP_Error;
use WC_Order_Query;
use WP_Query;
use WC_Product_Query;

class B4B_WC_Front {

	public function __construct() {
		add_filter( "woocommerce_add_cart_item_data", [
			$this,
			"maybe_empty_cart",
		], 1, 2 );

		add_action( "parse_request", [
			$this,
			"auth_pages_redirection",
		] );

		add_action( "woocommerce_created_customer", [
			$this,
			"extended_client_registration",
		], 10, 3 );

		add_filter( "woocommerce_login_redirect", [
			$this,
			"redirect_to_checkout_after_auth",
		], 99, 1 );

		add_filter( "woocommerce_registration_redirect", [
			$this,
			"redirect_to_checkout_after_auth",
		], 99, 1 );

		add_action( "woocommerce_add_to_cart", [
			$this,
			"skip_cart_for_events_and_donations",
		], 99, 6 ); //do as last in a row that will allow

		add_filter( "woocommerce_checkout_fields", [
			$this,
			"customize_checkout_fields_per_product_type",
		] );

		add_filter( 'woocommerce_form_field_heading', [ $this, 'woocommerce_form_field_heading' ], 10, 4 );

		add_action( "woocommerce_after_order_notes", [
			$this,
			"show_b4b_checkout_fields_after_order_notes",
		] );

		add_action( "woocommerce_single_product_summary", [
			$this,
			"override_single_product_summary",
		], 35 );

		add_action( "b4b_woocommerce_register_form", [
			$this,
			"show_tshirt_and_hat_fields_on_registration",
		] );

		add_action( 'b4b_woocommerce_after_customer_register_form', [ $this, 'registration_check_email_phone' ] );

		add_filter( 'woocommerce_process_registration_errors', [ $this, 'woocommerce_process_registration_errors' ] );

		add_filter( 'woocommerce_registration_error_email_exists', [ $this, 'woocommerce_registration_error_email_exists' ] );

		add_action( 'wp_ajax_b4b_registration_check', [ $this, 'ajax_registration_check' ] );
		add_action( 'wp_ajax_nopriv_b4b_registration_check', [ $this, 'ajax_registration_check' ] );

		add_action( "woocommerce_account_dashboard", [
			$this,
			"account_dashboard_show_donation_list",
		] );

		add_action( "woocommerce_edit_account_form", [
			$this,
			"show_tshirt_and_hat_fields_on_account_details",
		] );

		add_action( "woocommerce_save_account_details", [
			$this,
			"save_tshirt_and_hat_fields_on_account_details",
		], 20 );
	}

	public function auth_pages_redirection( $query ) {
		$my_account_page = get_post( get_option( "woocommerce_myaccount_page_id" ) );
		$cart_page       = get_post( get_option( "woocommerce_cart_page_id" ) );
		$checkout_page   = get_post( get_option( "woocommerce_checkout_page_id" ) );

		if ( is_user_logged_in() === true && strpos( $query->request, "auth" ) === 0 ) {
			wp_redirect( get_permalink( $my_account_page ) );
			exit;
		} elseif ( is_user_logged_in() === false && in_array( $query->request, [
				$cart_page->post_name,
				$checkout_page->post_name,
			] ) && b4b_have_event_in_cart( [
				"event_100_holes",
				"event_scramble",
			] ) ) {
			wp_redirect( get_permalink( get_page_by_path( "auth/login" ) ) );
			exit;
		} elseif ( is_user_logged_in() === false && in_array( $query->request, [
				$my_account_page->post_name,
			] ) ) {
			wp_redirect( get_permalink( get_page_by_path( "auth/login" ) ) );
			exit;
		}
	}

	public function change_lostpassword_url( $lostpassword_url, $redirection ) {
		if ( shortcode_exists( "b4b_auth_lost_password" ) === true ) {
			$lostpassword_url = get_permalink( get_page_by_path( "auth/lost-password" ) );
		}

		return $lostpassword_url;
	}

	public function extended_client_registration( $customer_id, $new_customer_data, $password_generated ) {
		$user = get_user_by( "ID", $customer_id );

		$user->first_name = sanitize_text_field( $_POST["account_first_name"] );
		$user->last_name  = sanitize_text_field( $_POST["account_last_name"] );

		wp_update_user( $user );

		update_user_meta( $customer_id, "billing_first_name", $user->first_name );
		update_user_meta( $customer_id, "billing_last_name", $user->last_name );
		update_user_meta( $customer_id, "billing_phone", sanitize_text_field( $_POST["billing_phone"] ) );
		update_user_meta( $customer_id, "billing_email", $user->user_email );

		update_user_meta( $customer_id, "b4b_tshirt_size", sanitize_text_field( $_POST["b4b_tshirt_size"] ) );
		update_user_meta( $customer_id, "b4b_hat_size", sanitize_text_field( $_POST["b4b_hat_size"] ) );

		update_user_meta( $customer_id, "shipping_first_name", $user->first_name );
		update_user_meta( $customer_id, "shipping_last_name", $user->last_name );
		update_user_meta( $customer_id, "show_admin_bar_front", false );

		// Friendly name for share link
		$name_slug_counter = '';
		$name_slug = sanitize_title( $user->first_name . ' ' . $user->last_name );
		do {
			// Dedup
			$name_slug .= $name_slug_counter;

			$matching_golfers = get_users( [
				'meta_key'   => 'b4b_name_slug',
				'meta_value' => $name_slug,
			] );

			$name_slug_counter++;
		} while ( !empty( $matching_golfers ) );

		update_user_meta( $customer_id, 'b4b_name_slug', $name_slug );
	}

	public function redirect_to_checkout_after_auth( $redirect ) {
		$redirect_to_checkout = false;
		if ( b4b_have_event_in_cart() ) {
			$redirect_to_checkout = true;
		}

		if ( $redirect_to_checkout === true ) {
			$redirect = get_permalink( get_option( "woocommerce_checkout_page_id" ) );
		} else {
			$redirect = get_permalink( get_option( "woocommerce_myaccount_page_id" ) );
		}

		return $redirect;
	}

	/**
	 * Heading - Custom field type for WC forms
	 * @param string $field
	 * @param string $key
	 * @param array $args
	 * @param string $value
	 */
	public function woocommerce_form_field_heading( $field, $key, $args, $value ) {
		echo '<h3 class="form-row form-row-wide form-field-heading">' . __( $args['label'], B4B_TEXT_DOMAIN ) . '</h3>';
	}

	public function customize_checkout_fields_per_product_type( $fields ) {
		if ( b4b_have_event_in_cart() === true || b4b_have_donation_in_cart() === true ) {
			unset( $fields["billing"]["billing_company"] );
			unset( $fields["billing"]["billing_country"] );
			unset( $fields["billing"]["billing_address_1"] );
			unset( $fields["billing"]["billing_address_2"] );
			unset( $fields["billing"]["billing_city"] );
			unset( $fields["billing"]["billing_state"] );
			unset( $fields["billing"]["billing_postcode"] );
			unset( $fields["billing"]["billing_phone"] );
		}

		if ( b4b_have_donation_in_cart() ) {
			$fields["order"]["order_comments"]["label"]       = __( "Donation notes", B4B_TEXT_DOMAIN );
			$fields["order"]["order_comments"]["placeholder"] = __( "Add message to golfer", B4B_TEXT_DOMAIN );
		}

		if ( b4b_have_event_in_cart( [
			"event_100_holes",
			"event_scramble",
		] ) ) {
			unset( $fields["order"]["order_comments"] );

			$fields["b4b_customer"] = [
				"b4b_tshirt_size" => [
					"type"     => "select",
					"label"    => __( "T-Shirt size", B4B_TEXT_DOMAIN ),
					"required" => true,
					"options"  => b4b_tshirt_sizes(),
				],
				"b4b_hat_size"    => [
					"type"     => "select",
					"label"    => __( "Hat size", B4B_TEXT_DOMAIN ),
					"required" => true,
					"options"  => b4b_hat_sizes(),
				],
			];
		}
		if ( b4b_have_event_in_cart( "event_scramble" ) ) {
			$fields['b4b_scramble_team'] = [];
			$priority = 0;
			for ( $i = 2; $i <= 4; $i++ ) {
				$fields['b4b_scramble_team']['b4b_scramble_team_members[' . $i . '][heading]'] = [
					'priority' => $priority++,
					'type'     => 'heading',
					'label'    => sprintf( __( 'Team Member %d', B4B_TEXT_DOMAIN ), $i ),
				];

				$fields['b4b_scramble_team']['b4b_scramble_team_members[' . $i . '][name]'] = [
					'priority' => $priority++,
					'type'     => 'text',
					'label'    => __( 'Full Name', B4B_TEXT_DOMAIN ),
				];
				$fields['b4b_scramble_team']['b4b_scramble_team_members[' . $i . '][email]'] = [
					'priority' => $priority++,
					'type'     => 'text',
					'label'    => __( 'Email', B4B_TEXT_DOMAIN ),
				];
				$fields['b4b_scramble_team']['b4b_scramble_team_members[' . $i . '][phone]'] = [
					'priority' => $priority++,
					'type'     => 'text',
					'label'    => __( 'Phone', B4B_TEXT_DOMAIN ),
				];
				$fields['b4b_scramble_team']['b4b_scramble_team_members[' . $i . '][tshirt_size]'] = [
					'priority' => $priority++,
					'type'     => 'select',
					'label'    => __( 'T-Shirt Size', B4B_TEXT_DOMAIN ),
					'options'  => b4b_tshirt_sizes(),
				];
				$fields['b4b_scramble_team']['b4b_scramble_team_members[' . $i . '][hat_size]'] = [
					'priority' => $priority++,
					'type'     => 'select',
					'label'    => __( 'Hat Size', B4B_TEXT_DOMAIN ),
					'options'  => b4b_hat_sizes(),
				];
			}
		}


		return $fields;
	}

	public function show_b4b_checkout_fields_after_order_notes( $checkout ) {

		if ( b4b_have_event_in_cart() === false ) {
			return;
		}

		echo "<div id=\"my_customer_fields\">";

		if ( isset( $checkout->checkout_fields["b4b_customer"] ) ) {
			foreach ( $checkout->checkout_fields["b4b_customer"] as $key => $field ) {
				woocommerce_form_field( $key, $field, get_user_meta( get_current_user_id(), $key, true ) );
			}
		}

		if ( b4b_have_event_in_cart( "event_scramble" ) ) {
			echo "<div class=\"scramble_team_member_block\">";
			foreach ( $checkout->checkout_fields["b4b_scramble_team"] as $key => $field ) {
				woocommerce_form_field( $key, $field );
			}
			echo "</div>";
		}

		echo "</div>";
	}


	public function override_single_product_summary() {
		global $product;
		if ( ( $product->is_purchasable() === false ) && b4b_is_product_event( $product, [
				"event_scramble",
				"event_100_holes",
			] ) ) {
			$notif_class     = "no-reg-notif";
			$msg             = "You have already registered, <a href='http://localhost/b4b/contact-us-to-get-involved/'>contact us</a> if you have any questions";
			$product_ordered = false;

			if ( is_user_logged_in() ) {
				$query = new WC_Order_Query( [
					"status"         => [ "wc-completed", "wc-on-hold" ],
					"type"           => [ "shop_order" ],
					"customer_id"    => get_current_user_id(),
					"posts_per_page" => - 1,
				] );

				$orders = $query->get_orders();
				if ( count( $orders ) > 0 ) {
					//check only if customer orders exist
					/** @var $order WC_Order */
					foreach ( $orders as $order ) {
						$items = $order->get_items();
						foreach ( $items as $item ) {
							if ( $item["product_id"] === $product->get_id() ) {
								//this user has already donated to access this event
								$product_ordered = true;
								$msg             = "You’ve already registered";
								break;
							}
						}
						if ( $product_ordered ) {
							break;
						}
					}
				}
			}

			if ( ! $product_ordered ) {
				$meta_start_date = get_post_meta( $product->get_id(), "_b4b_event_registration_start_date", true );
				$meta_end_date   = get_post_meta( $product->get_id(), "_b4b_event_registration_end_date", true );

				if ( $meta_start_date && $meta_end_date ) {
					$today      = date_create();
					$start_date = date_create_from_format( "Y-m-d", $meta_start_date );
					$end_date   = date_create_from_format( "Y-m-d", $meta_end_date );
					if ( $today < $start_date ) {
						$msg         = "Registration has not started yet";
						$notif_class .= " no-reg-not-active";
					} else if ( $today > $end_date ) {
						$msg         = "Registration period is finished";
						$notif_class .= " no-reg-not-active";
					}
				}
			}

			echo "<p class=\"" . $notif_class . "\">" . $msg . "</p>";
		}
	}

	public function show_tshirt_and_hat_fields_on_registration() {
		ob_start();

		woocommerce_form_field( "b4b_tshirt_size", [
			"id"      => "b4b_tshirt_size",
			"type"    => "select",
			"label"   => __( "T-Shirt size", B4B_TEXT_DOMAIN ),
			"class"   => [ "form-row", "form-row-first" ],
			"options" => b4b_tshirt_sizes(),
		] );

		woocommerce_form_field( "b4b_hat_size", [
			"id"      => "b4b_hat_size",
			"type"    => "select",
			"label"   => __( "Hat size", B4B_TEXT_DOMAIN ),
			"class"   => [ "form-row", "form-row-last" ],
			"options" => b4b_hat_sizes(),
		] );

		$html = ob_get_clean();

		echo $html;
	}

	public function registration_check_email_phone() {
		wc_get_template( 'woocommerce/myaccount/form-b4b-register-unique.php', [], '', B4B_PLUGIN_PATH . '/templates/' );
	}

	/**
	 * Add login link to email already exists error message.
	 * @param string $message
	 * @return string
	 */
	public function woocommerce_registration_error_email_exists( $message ) {
		return sprintf( 'An account is already registered with your email address. Please <a href="%s">log in</a>.', wc_get_endpoint_url( 'login', '', wc_get_page_permalink( 'myaccount' ) ) );
	}

	/**
	 * Check if phone number is already in the system and return error.
	 * @param WP_Error $validation_error
	 * @return WP_Error
	 */
	public function woocommerce_process_registration_errors( $validation_error ) {

		$phone = isset( $_POST['billing_phone'] ) ? wp_unslash( $_POST['billing_phone'] ) : '';
		if ( !empty( $phone ) ) {
			$users = get_users( [
				'meta_key'    => 'billing_phone',
				'meta_value'  => $phone,
				'number'      => 1,
				'count_total' => false
			] );
			if ( !empty( $users ) ) {
				$message = sprintf( 'An account is already registered with your phone number. Please <a href="%s">log in</a>.', wc_get_endpoint_url( 'login', '', wc_get_page_permalink( 'myaccount' ) ) );
				return new WP_Error( 'registration-error-phone-exists', $message );
			}
		}

		return $validation_error;
	}

	/**
	 * Check if user with email and phone already exists
	 */
	public function ajax_registration_check() {
		$email = isset( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : '';
		$phone = isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '';


		$email_exists = !empty( $email ) && email_exists( $email );

		$phone_exists = false;
		if ( !empty( $phone ) ) {
			$users = get_users( [
				'meta_key'    => 'billing_phone',
				'meta_value'  => $phone,
				'number'      => 1,
				'count_total' => false
			] );
			if ( !empty( $users ) ) {
				$phone_exists = true;
			}
		}

		if ( $email_exists && $phone_exists ) {
			$message = 'An account is already registered with your email and phone number.';
		} elseif ( $email_exists ) {
			$message = 'An account is already registered with your email address.';
		} elseif ( $phone_exists ) {
			$message = 'An account is already registered with your phone number.';
		} else {
			$message = '';
		}

		if ( $email_exists && session_start() ) {
			$_SESSION['email_for_reset_password'] = $email;
		}

		wp_send_json( [
			'exists'  => $email_exists || $phone_exists,
			'message' => $message,
		] );
		// wp_die();
	}

	public function show_tshirt_and_hat_fields_on_account_details() {
		ob_start();

		echo "<fieldset>";
		echo "<legend>" . __( "Customer details", B4B_TEXT_DOMAIN ) . "</legend>";

		woocommerce_form_field( "b4b_tshirt_size", [
			"id"      => "b4b_tshirt_size",
			"type"    => "select",
			"label"   => __( "T-Shirt size", B4B_TEXT_DOMAIN ),
			"class"   => [ "form-row", "form-row-first" ],
			"options" => b4b_tshirt_sizes(),
		], get_user_meta( get_current_user_id(), "b4b_tshirt_size", true ) );

		woocommerce_form_field( "b4b_hat_size", [
			"id"      => "b4b_hat_size",
			"type"    => "select",
			"label"   => __( "Hat size", B4B_TEXT_DOMAIN ),
			"class"   => [ "form-row", "form-row-last" ],
			"options" => b4b_hat_sizes(),
		], get_user_meta( get_current_user_id(), "b4b_hat_size", true ) );

		echo "</fieldset>";
		echo "<div class=\"clear\"></div>";

		$html = ob_get_clean();

		echo $html;
	}

	public function save_tshirt_and_hat_fields_on_account_details() {
		$customer_id = get_current_user_id();

		$fields = [
			"b4b_tshirt_size",
			"b4b_hat_size",
		];

		foreach ( $fields as $field ) {
			if ( isset( $_REQUEST[ $field ] ) === true ) {
				update_user_meta( $customer_id, $field, sanitize_text_field( $_REQUEST[ $field ] ) );
			}
		}
	}

	function maybe_empty_cart( $cart_item_data, $product_id ) {
		$product = wc_get_product( $product_id );

		if ( b4b_is_product_event( $product ) || b4b_is_production_donation( $product ) ) {
			if ( b4b_is_product_event( $product, "event_party" ) === false
				 || b4b_have_event_in_cart( "event_party" ) === false ) {
				WC()->cart->empty_cart();
			}

		}

		if ( b4b_is_production_donation( $product ) === false && b4b_is_product_event( $product ) === false && (
				b4b_have_event_in_cart() === true || b4b_have_donation_in_cart() === true
			) ) {
			if ( b4b_is_product_event( $product, "event_party" ) === false
				 || b4b_have_event_in_cart( "event_party" ) === false ) {
				WC()->cart->empty_cart();
			}
		}

		return $cart_item_data;
	}

	public function skip_cart_for_events_and_donations( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		$product = wc_get_product( $product_id );
		if ( b4b_is_production_donation( $product ) || b4b_is_product_event( $product ) ) {
			wp_redirect( get_permalink( get_option( "woocommerce_checkout_page_id" ) ) );
			exit;
		}
	}

	public function account_dashboard_show_donation_list() {

		$min_year = 2016;
		$max_year = intval( date( "Y" ) );

		$show_all      = ( sanitize_text_field( $_REQUEST["show"] ?? '' ) === "all" );
		$selected_year = intval( sanitize_text_field( $_REQUEST["filter"] ?? '' ) );
		if ( empty( $selected_year ) === true && $show_all === false ) {
			$selected_year = $max_year;
		}

		if ( $show_all === false ) {
			$date_range = sprintf( "%d-01-01...%d-12-31", $selected_year, $selected_year );
		} else {
			$date_range = sprintf( "%d-01-01...%d-12-31", $min_year, $max_year );
		}

		//find active donation product (adp)
		$adp_query = new WC_Product_Query( [
			"status"                => "publish",
			"type"                  => "donation",
			"donation_opened_range" => $date_range,
			"posts_per_page"        => - 1,
		] );

		$adps = $adp_query->get_products();
		$adp  = null;
		if ( $adps >= 1 ) {
			$adp = current( $adps );
		}

		$donations_query = new WC_Order_Query( [
			"status"            => [ "completed", "processing" ],
			"date_paid"         => $date_range,
			"donated_golfer_id" => get_current_user_id(),
			"posts_per_page"    => - 1,
		] );

		$donations = $donations_query->get_orders();

		$donations_goal = 0.00;
		$donation_link  = null;
		if ( $adp ) {
			$donations_goal = get_post_meta( $adp->get_id(), "_b4b_donation_goal", true );
			if ( is_user_logged_in() === true ) {
				$donation_link = get_permalink( $adp->get_id() );

				$current_user_id = get_current_user_id();
				$b4b_name_slug = get_user_meta( $current_user_id, 'b4b_name_slug', true );
				$for = !empty( $b4b_name_slug ) ? $b4b_name_slug : $current_user_id;

				$donation_link .= "?for=" . $for;
			}
		}
		$donations_list      = [];
		$donations_collected = 0;
		/**
		 * @var $donation \WC_Order
		 */
		foreach ( $donations as $donation ) {

			$donations_list[] = [
				"date"       => $donation->get_date_paid(),
				"donor_name" => sprintf( "%s %s", $donation->get_billing_first_name(), $donation->get_billing_last_name() ),
				"message"    => $donation->get_customer_note(),
				"amount"     => floatval( $donation->get_total() ),
			];

			$donations_collected += floatval( $donation->get_total() );
		}

		wc_get_template( "woocommerce/myaccount/donation-list.php", [
			"min_year"            => $min_year,
			"max_year"            => $max_year,
			"selected_year"       => $selected_year,
			"donations_list"      => $donations_list,
			"donations_goal"      => $donations_goal,
			"donations_collected" => $donations_collected,
			"donation_link"       => $donation_link,
		], "", B4B_PLUGIN_PATH . "/templates/" );
	}

	public function handle_donated_golfer_query_var( $query, $query_vars ) {
		if ( empty( $query_vars["donated_golfer_id"] ) === false ) {
			$query["meta_query"][] = [
				"key"   => "_b4b_donated_golfer_id",
				"value" => esc_attr( $query_vars["donated_golfer_id"] ),
			];
		}

		return $query;
	}

	function handle_donation_opened_range_query_var( $query, $query_vars ) {
		if ( empty( $query_vars["donation_opened_range"] ) === false ) {
			$query["meta_query"][] = [
				"relation" => "and",
				[
					"key"     => "_b4b_donation_start_date",
					"value"   => explode( "...", $query_vars["donation_opened_range"] ),
					"compare" => "between",
					"type"    => "date",
				],
				[
					"key"     => "_b4b_donation_end_date",
					"value"   => explode( "...", $query_vars["donation_opened_range"] ),
					"compare" => "between",
					"type"    => "date",
				],
			];
		}

		return $query;
	}
}