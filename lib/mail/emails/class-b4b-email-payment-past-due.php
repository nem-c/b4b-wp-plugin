<?php

namespace B4B_Theme_Support\Lib\Mail\Emails;

use B4B_Theme_Support\Lib\Mail\B4B_Mail;

/**
 * An email sent to the customers when they have past due payments.
 */
class B4B_Email_Payment_Past_Due extends B4B_Email
{

	/**
	 * Fetch data for sending emails
	 * @global type $wpdb
	 */
	private function get_data()
	{
		$data = [];

		// List of people to email
		$accounts = self::get_accounts_from_orders();
		foreach ( $accounts as $account ) {
			$events = [];
			if ( $account['event_100_holes'] && !$account['event_100_holes_paid'] ) {
				$order = $account['event_100_holes_order'];
				if ( $order->get_status() === 'on-hold' ) {
					foreach ( $order->get_items() as $order_item ) {
						$product = $order_item->get_product();
					}

					$events[] = [
						'name'         => $product->name,
						'amount'       => B4B_Mail::price_format( $account['event_100_holes_fee'] ),
						'paid'         => B4B_Mail::price_format( $account['event_100_holes_paid'] ? $account['event_100_holes_fee'] : 0 ),
						'make_payment' => $order->get_checkout_payment_url(),
					];
				}
			}
			if ( $account['event_scramble'] && $account['event_scramble_captain'] && !$account['event_scramble_paid'] ) {
				$order = $account['event_scramble_order'];
				if ( $order->get_status() === 'on-hold' ) {
					foreach ( $order->get_items() as $order_item ) {
						$product = $order_item->get_product();
					}

					$events[] = [
						'name'         => $product->name,
						'amount'       => B4B_Mail::price_format( $account['event_scramble_fee'] ),
						'paid'         => B4B_Mail::price_format( $account['event_scramble_paid'] ? $account['event_scramble_fee'] : 0 ),
						'make_payment' => $order->get_checkout_payment_url(),
					];
				}
			}
//			if ( $account['event_party'] && !$account['event_scramble_captain'] ) {
//				$order = $account['event_party_order'];
//				if ( $order->get_status() === 'on-hold' ) {
//					foreach ( $order->get_items() as $order_item ) {
//						$product = $order_item->get_product();
//					}
//
//					$events[] = [
//						'name'         => $product->name,
////						'amount'       => B4B_Mail::price_format( $account['event_scramble_fee'] ),
////						'paid'         => B4B_Mail::price_format( $account['event_scramble_paid'] ? $account['event_scramble_fee'] : 0 ),
//						'make_payment' => $order->get_checkout_payment_url(),
//					];
//				}
//			}

			if ( !empty( $events ) ) {
				$data[] = [
					'recipient'  => $account['contact_email'],
					'first_name' => get_user_meta( $account['user_id'], 'first_name', true ),
					'events'     => $events,
				];
			}
		}

		return $data;
	}

	/**
	 * Get email heading.
	 * @return string
	 */
	public function get_default_heading()
	{
		return __( "You have a payment that's past due", 'b4b-theme-support' );
	}

	/**
	 * Trigger the sending of all emails.
	 * @return void
	 */
	public function trigger_all()
	{
		if ( !$this->is_enabled() ) {
			return;
		}

		$this->setup_locale();

		$items = $this->get_data();
		foreach ( $items as $item ) {
			$this->recipient = $item['recipient'];

			// DEV: Sends email to dev account
			if ( self::IS_TEST_MODE ) {
				$this->recipient = self::DEV_EMAIL;
			}

			if ( $this->get_recipient() ) {
				// Fill data
				$this->args = $item;

				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			// DEV: Sends only one email for testing purposes
			if ( self::IS_TEST_MODE ) {
				break;
			}
		}

		$this->restore_locale();
	}

	/**
	 * Preview this email.
	 * @return string
	 */
	public function get_preview_html()
	{
		$this->setup_locale();


		// Fill fake data
		$this->args = [
			'first_name' => 'NAME',
			'events'     => [
				['name' => 'Event 1', 'amount' => '$1,100', 'paid' => '$0', 'make_payment' => '#'],
				['name' => 'Event 2', 'amount' => '$2,200', 'paid' => '$0', 'make_payment' => '#'],
			],
		];


		$html = $this->get_content();

		$this->restore_locale();

		return $html;
	}

}
