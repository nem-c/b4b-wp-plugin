<?php

function b4b_scramble_team_meta_blueprint() {
	return [
		2 => [
			"name"        => null,
			"email"       => null,
			"phone"       => null,
			"tshirt_size" => null,
			"hat_size"    => null,
		],
		3 => [
			"name"        => null,
			"email"       => null,
			"phone"       => null,
			"tshirt_size" => null,
			"hat_size"    => null,
		],
		4 => [
			"name"        => null,
			"email"       => null,
			"phone"       => null,
			"tshirt_size" => null,
			"hat_size"    => null,
		]
	];
}

function b4b_scramble_team_meta_get( $order_id ) {
	$team_meta_blueprint = b4b_scramble_team_meta_blueprint();

	if ( metadata_exists( 'post', $order_id, '_b4b_scramble_team_members' ) ) {
		$team_meta = array_replace_recursive( $team_meta_blueprint, get_post_meta( $order_id, '_b4b_scramble_team_members', true ) );
	} else {
		$team_meta = $team_meta_blueprint;
	}

	return $team_meta;
}

function b4b_scramble_team_meta_set( $order_id ) {
	$data = $_POST[ 'b4b_scramble_team_members' ];

	array_walk_recursive( $data, 'sanitize_text_field' );
	update_post_meta( $order_id, '_b4b_scramble_team_members', $data );
}

function b4b_have_event_in_order( $order_id, $event_type = [ "event_scramble", "event_100_holes", "event_party" ] ) {
	return b4b_have_product_type_in_order( $order_id, $event_type );
}

function b4b_have_donation_in_order( $order_id ) {
	return b4b_have_product_type_in_order( $order_id, "donation" );
}

function b4b_have_product_type_in_order( $order_id, $product_type ) {
	$have_event = false;

	$order = wc_get_order( $order_id );
	if ( $order ) {
		foreach ( $order->get_items() as $order_item ) {
			$product    = $order_item->get_product();
			$have_event = b4b_is_product_event( $product, $product_type );
		}
	}

	return $have_event;
}

function b4b_tshirt_sizes( $first_empty = true, $first_empty_text = "Select" ) {
	$sizes = [];
	if ( $first_empty === true ) {
		$sizes[""] = __( $first_empty_text, B4B_TEXT_DOMAIN );
	}
	$sizes["S"]   = __( "Small", B4B_TEXT_DOMAIN );
	$sizes["M"]   = __( "Medium", B4B_TEXT_DOMAIN );
	$sizes["L"]   = __( "Large", B4B_TEXT_DOMAIN );
	$sizes["XL"]  = __( "XLarge", B4B_TEXT_DOMAIN );
	$sizes["XXL"] = __( "XXLarge", B4B_TEXT_DOMAIN );

	return $sizes;
}

function b4b_hat_sizes( $first_empty = true, $first_empty_text = "Select" ) {
	$sizes = [];
	if ( $first_empty === true ) {
		$sizes[""] = __( $first_empty_text, B4B_TEXT_DOMAIN );
	}
	$sizes["M/L"]  = __( "Medium/Large", B4B_TEXT_DOMAIN );
	$sizes["L/XL"] = __( "Large/XLarge", B4B_TEXT_DOMAIN );

	return $sizes;
}

function b4b_is_product_event( $product, $event_type = [ "event_scramble", "event_100_holes", "event_party" ] ) {
	if (empty($product) === true) {
		return false;
	}
	if ( is_array( $event_type ) === false ) {
		$event_type = [ $event_type ];
	}

	return in_array( $product->get_type(), $event_type );
}

function b4b_is_production_donation( $product ) {
	if (empty($product) === true) {
		return false;
	}
	return in_array( $product->get_type(), [ "donation" ] );
}

function b4b_is_customer_registered_for_event( $product_event, $customer_id = 0 ) {
	$registered = false;

	if ( $customer_id === 0 ) {
		$customer_id = get_current_user_id();
	}

	$query = new WC_Order_Query( [
		"status"         => [ "completed", "on-hold", "processing" ],
		"type"           => [ "shop_order" ],
		"customer_id"    => $customer_id,
		"posts_per_page" => - 1,
	] );

	$orders = $query->get_orders();
	if ( count( $orders ) > 0 ) {
		//check only if customer orders exist
		/** @var $order WC_Order */
		foreach ( $orders as $order ) {
			$items = $order->get_items();
			foreach ( $items as $item ) {
				if ( $item["product_id"] === $product_event->get_id() ) {
					$registered = true;
				}
			}
		}
	}


	return $registered;
}

function b4b_have_event_in_cart( $event_type = [ "event_100_holes", "event_scramble", "event_party" ] ) {
	if ( is_array( $event_type ) === false ) {
		$event_type = [ $event_type ];
	}

	return b4b_have_product_types_in_cart( $event_type );
}

function b4b_have_donation_in_cart() {
	return b4b_have_product_types_in_cart( [ "donation" ] );
}

function b4b_have_product_types_in_cart( $product_types = [] ) {
	$event_product_in_cart = false;

	if ( WC()->cart ) {
		foreach ( WC()->cart->get_cart_contents() as $item ) {
			if ( in_array( $item["data"]->get_type(), $product_types ) ) {
				$event_product_in_cart = true;
			}
		}
	}

	return $event_product_in_cart;
}

function b4b_array_sort_by_column( &$arr, $col, $dir = SORT_ASC ) {
	$sort_col = array();
	foreach ( $arr as $key => $row ) {
		$sort_col[ $key ] = strtolower($row[ $col ]);
	}

	array_multisort( $sort_col, $dir, $arr );
}

/**
 * Email login credentials to a newly-registered user.
 *
 * A new user registration notification is also sent to admin email.
 *
 * @since 2.0.0
 * @since 4.3.0 The `$plaintext_pass` parameter was changed to `$notify`.
 * @since 4.3.1 The `$plaintext_pass` parameter was deprecated. `$notify` added as a third parameter.
 * @since 4.6.0 The `$notify` parameter accepts 'user' for sending notification only to the user created.
 *
 * @global wpdb         $wpdb      WordPress database object for queries.
 * @global PasswordHash $wp_hasher Portable PHP password hashing framework instance.
 *
 * @param int    $user_id    User ID.
 * @param null   $deprecated Not used (argument deprecated).
 * @param string $notify     Optional. Type of notification that should happen. Accepts 'admin' or an empty
 *                           string (admin only), 'user', or 'both' (admin and user). Default empty.
 */
function wp_new_user_notification( $user_id, $deprecated = null, $notify = '' ) {
	if ( $deprecated !== null ) {
		_deprecated_argument( __FUNCTION__, '4.3.1' );
	}

	// Accepts only 'user', 'admin' , 'both' or default '' as $notify
	if ( ! in_array( $notify, array( 'user', 'admin', 'both', '' ), true ) ) {
		return;
	}

	global $wpdb, $wp_hasher;
	$user = get_userdata( $user_id );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

	if ( 'user' !== $notify ) {
		$switched_locale = switch_to_locale( get_locale() );

		/* translators: %s: site title */
		$message = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
		/* translators: %s: user login */
		$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
		/* translators: %s: user email address */
		$message .= sprintf( __( 'Email: %s' ), $user->user_email ) . "\r\n";

		$wp_new_user_notification_email_admin = array(
			'to'      => get_option( 'admin_email' ),
			/* translators: New user registration notification email subject. %s: Site title */
			'subject' => __( '[%s] New User Registration' ),
			'message' => $message,
			'headers' => '',
		);

		/**
		 * Filters the contents of the new user notification email sent to the site admin.
		 *
		 * @since 4.9.0
		 *
		 * @param array   $wp_new_user_notification_email {
		 *     Used to build wp_mail().
		 *
		 *     @type string $to      The intended recipient - site admin email address.
		 *     @type string $subject The subject of the email.
		 *     @type string $message The body of the email.
		 *     @type string $headers The headers of the email.
		 * }
		 * @param WP_User $user     User object for new user.
		 * @param string  $blogname The site title.
		 */
		$wp_new_user_notification_email_admin = apply_filters( 'wp_new_user_notification_email_admin', $wp_new_user_notification_email_admin, $user, $blogname );

		@wp_mail(
			$wp_new_user_notification_email_admin['to'],
			wp_specialchars_decode( sprintf( $wp_new_user_notification_email_admin['subject'], $blogname ) ),
			$wp_new_user_notification_email_admin['message'],
			$wp_new_user_notification_email_admin['headers']
		);

		if ( $switched_locale ) {
			restore_previous_locale();
		}
	}

	// `$deprecated was pre-4.3 `$plaintext_pass`. An empty `$plaintext_pass` didn't sent a user notification.
	if ( 'admin' === $notify || ( empty( $deprecated ) && empty( $notify ) ) ) {
		return;
	}

//	// Generate something random for a password reset key.
//	$key = wp_generate_password( 20, false );
//
//	/** This action is documented in wp-login.php */
//	do_action( 'retrieve_password_key', $user->user_login, $key );
//
//	// Now insert the key, hashed, into the DB.
//	if ( empty( $wp_hasher ) ) {
//		require_once ABSPATH . WPINC . '/class-phpass.php';
//		$wp_hasher = new PasswordHash( 8, true );
//	}
//	$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
//	$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

//	$switched_locale = switch_to_locale( get_user_locale( $user ) );
//
//	/* translators: %s: user login */
//	$message  = sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
//	$message .= __( 'To set your password, visit the following address:' ) . "\r\n\r\n";
//	$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . ">\r\n\r\n";
//
//	$message .= wp_login_url() . "\r\n";
//
//	$wp_new_user_notification_email = array(
//		'to'      => $user->user_email,
//		/* translators: Login details notification email subject. %s: Site title */
//		'subject' => __( '[%s] Login Details' ),
//		'message' => $message,
//		'headers' => '',
//	);
//
//	/**
//	 * Filters the contents of the new user notification email sent to the new user.
//	 *
//	 * @since 4.9.0
//	 *
//	 * @param array   $wp_new_user_notification_email {
//	 *     Used to build wp_mail().
//	 *
//	 *     @type string $to      The intended recipient - New user email address.
//	 *     @type string $subject The subject of the email.
//	 *     @type string $message The body of the email.
//	 *     @type string $headers The headers of the email.
//	 * }
//	 * @param WP_User $user     User object for new user.
//	 * @param string  $blogname The site title.
//	 */
//	$wp_new_user_notification_email = apply_filters( 'wp_new_user_notification_email', $wp_new_user_notification_email, $user, $blogname );
//
//	wp_mail(
//		$wp_new_user_notification_email['to'],
//		wp_specialchars_decode( sprintf( $wp_new_user_notification_email['subject'], $blogname ) ),
//		$wp_new_user_notification_email['message'],
//		$wp_new_user_notification_email['headers']
//	);
//
//	if ( $switched_locale ) {
//		restore_previous_locale();
//	}

	// Handle password creation.
	$password_generated = false;

	$new_customer_data = [
		'user_login' => $user->user_login,
		// 'user_pass'  => $password,
		'user_email' => $user->user_email,
		'role'       => 'customer',
	];
	do_action( 'woocommerce_created_customer', $user_id, $new_customer_data, $password_generated );

}
