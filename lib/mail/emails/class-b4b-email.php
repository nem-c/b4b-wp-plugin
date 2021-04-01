<?php

namespace B4B_Theme_Support\Lib\Mail\Emails;

use WC_Email;
use WC_Order_Query;
use WC_Product_Query;

/**
 * B4B Email.
 *
 * Email sent to the customers.
 */
class B4B_Email extends WC_Email
{
	public $template_base = B4B_PLUGIN_PATH . '/templates/admin/mail/email/';

	protected $mail;
	public $args = [];

	/** @var bool Sends just one email to dev email */
	const IS_TEST_MODE = false;
	const DEV_EMAIL = '';

	const ALLOWED_EVENT_TYPES = [
		'event_scramble',
		'event_100_holes',
		'event_party',
	];

	/**
	 * Constructor.
	 */
	public function __construct( $mail )
	{
		$this->mail = $mail;

		$this->id             = $this->mail->type;
		$this->customer_email = true;

		$this->title          = $this->mail->type_label;
		$this->description    = '';

		$this->template_html  = "email-{$this->mail->type}.php";
		$this->template_plain = "plain/{$this->template_html}";
		$this->subject        = $this->mail->email_subject;

		// Call parent constructor.
		parent::__construct();
	}

	/**
	 * Returns accounts
	 * @return array
	 */
	protected static function get_accounts_from_orders()
	{
		$year = gmdate( 'Y' );


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


		// Skip if there are no users
		if ( empty( $user_ids ) ) {
			return;
		}


		// Fetch users (+ meta data)
		$users = get_users( [
			'include' => $user_ids,
			'fields'  => 'all_with_meta',
		] );


		$accounts = [];
		foreach ( $users as $user ) {
			$user_id = (int) $user->ID;
			$accounts[ $user_id ] = [
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

				if ( ! $accounts[ $customer_id ]['event_100_holes'] && isset( $order_atts[ $order_id ]['event_100_holes'] ) ) {
					$accounts[ $customer_id ]['event_100_holes']                   = true;
					$accounts[ $customer_id ]['event_100_holes_paid']              = $order->is_paid();
					$accounts[ $customer_id ]['event_100_holes_fee']               = $order->get_total();
					$accounts[ $customer_id ]['event_100_holes_order_url']         = $order->get_edit_order_url();
					$accounts[ $customer_id ]['event_100_holes_donation_goal']     = isset( $user_atts[ $customer_id ]['event_100_holes_donation_goal'] ) ? $user_atts[ $customer_id ]['event_100_holes_donation_goal'] : 0;
					$accounts[ $customer_id ]['event_100_holes_money_raised']      = isset( $user_atts[ $customer_id ]['event_100_holes_money_raised'] ) ? $user_atts[ $customer_id ]['event_100_holes_money_raised'] : 0;
					$accounts[ $customer_id ]['event_100_holes_donation_goal_met'] = ( $accounts[ $customer_id ]['event_100_holes_money_raised'] > $accounts[ $customer_id ]['event_100_holes_donation_goal'] );
					$accounts[ $customer_id ]['event_100_holes_order']             = $order;
				}

				if ( isset( $order_atts[ $order_id ]['event_scramble'] ) ) {
					if ( ! $accounts[ $customer_id ]['event_scramble'] ) {
						$accounts[ $customer_id ]['event_scramble']                = true;
						$accounts[ $customer_id ]['event_scramble_captain']        = true;
						$accounts[ $customer_id ]['event_scramble_captain_id']     = false;
						$accounts[ $customer_id ]['event_scramble_paid']           = $order->is_paid();
						$accounts[ $customer_id ]['event_scramble_fee']            = $order->get_total();
						$accounts[ $customer_id ]['event_scramble_order_url']      = $order->get_edit_order_url();
						$accounts[ $customer_id ]['event_scramble_captain_name']   = $accounts[ $customer_id ]['name'];
						$accounts[ $customer_id ]['event_scramble_order']          = $order;
					}

					self::get_accounts_from_scramble( $accounts, $order );
				}

//				if ( ! $accounts[ $customer_id ]['event_party'] && isset( $order_atts[ $order_id ]['event_party'] ) ) {
//					$accounts[ $customer_id ]['event_party']                  = true;
//					$accounts[ $customer_id ]['event_party_tickets_quantity'] = $user_atts[ $customer_id ]['event_party_tickets_quantity'];
//					$accounts[ $customer_id ]['event_party_order']            = $order;
//				}
			}
		}

		return $accounts;
	}

	/**
	 * Get additional scramble accounts
	 * @param array $accounts
	 * @param object $order
	 * @return void
	 */
	private static function get_accounts_from_scramble( &$accounts, $order )
	{
		$captain_user_id       = (int) $order->get_customer_id();
		$scramble_team_members = b4b_scramble_team_meta_get( $order->get_id() );

		foreach ( $scramble_team_members as $team_member_id => $team_member_data ) {
			$user_key = $captain_user_id . '.' . $team_member_id;
			if ( isset( $accounts[ $user_key ] ) ) {
				continue;
			}

			$accounts[ $user_key ] = [
				'user_id'       => 0,
				'name'          => ( empty( $team_member_data['name'] ) === false ) ? $team_member_data['name'] : '',
				'tshirt_size'   => $team_member_data['tshirt_size'],
				'hat_size'      => $team_member_data['hat_size'],
				'contact_email' => $team_member_data['email'],
				'contact_phone' => $team_member_data['phone'],

				'event_100_holes'              => false,
				'event_100_holes_fee'          => -1,
				'event_100_holes_paid'         => false,
				'event_100_holes_money_raised' => -1,

				'event_scramble'               => true,
				'event_scramble_fee'           => -1,
				'event_scramble_paid'          => false,
				'event_scramble_captain'       => false,
				'event_scramble_captain_id'    => $captain_user_id,
				'event_scramble_captain_name'  => $accounts[ $captain_user_id ]['name'],
				'event_scramble_order_url'     => '#account-' . $captain_user_id,

//				'event_party'                  => false,
//				'event_party_tickets_quantity' => -1,
			];
		}
	}

	/**
	 * Get content html.
	 *
	 * @return string
	 */
	public function get_content_html()
	{
		return wc_get_template_html( $this->template_html, [
			'args'          => $this->args,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => false,
			'email'         => $this,
		], '', $this->template_base );
	}

	//	/**
	//	 * Get content plain.
	//	 *
	//	 * @return string
	//	 */
	//	public function get_content_plain()
	//	{
	//		return wc_get_template_html( $this->template_plain, [
	//			'object'        => $this->object,
	//			'email_heading' => $this->get_heading(),
	//			'sent_to_admin' => false,
	//			'plain_text'    => true,
	//			'email'         => $this,
	//		], '', $this->template_base );
	//	}

}
