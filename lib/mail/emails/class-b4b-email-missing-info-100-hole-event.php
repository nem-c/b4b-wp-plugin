<?php

namespace B4B_Theme_Support\Lib\Mail\Emails;

/**
 * An email sent to the customers when they didn't fill in all the data for 100 holes event.
 */
class B4B_Email_Missing_Info_100_Hole_Event extends B4B_Email
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
			if ( $account['event_100_holes'] ) {
				$is_missing = empty( $account['name'] ) || empty( $account['contact_email'] ) || empty( $account['contact_phone'] ) || empty( $account['tshirt_size'] ) || empty( $account['hat_size'] );

				if ( $is_missing ) {
					$data[] = [
						'recipient'  => $account['contact_email'],
						'first_name' => get_user_meta( $account['user_id'], 'first_name', true ),

						'name'       => $account['name'],
						'email'      => $account['contact_email'],
						'phone'      => $account['contact_phone'],
						'shirt_size' => $account['tshirt_size'],
						'hat_size'   => $account['hat_size'],
					];
				}
			}
		}

		return $data;
	}

	/**
	 * Get email heading.
	 * @return string
	 */
	public function get_default_heading() {
		return __( "We're missing info on you for the 100 hole event", 'b4b-theme-support' );
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
			'name'       => 'NAME',
			'email'      => 'email@example.com',
			'phone'      => '012 345678',
			'shirt_size' => 'L',
			'hat_size'   => '',
		];


		$html = $this->get_content();

		$this->restore_locale();

		return $html;
	}

}
