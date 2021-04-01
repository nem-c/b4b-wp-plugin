<?php

namespace B4B_Theme_Support\Lib\Mail\Emails;

/**
 * An email sent to the customers when they didn't fill in all the data for scramble event.
 */
class B4B_Email_Missing_Info_Scramble_Team extends B4B_Email
{

	/**
	 * Fetch data for sending emails
	 * @global type $wpdb
	 */
	private function get_data()
	{
		$data = [];
		$accounts = self::get_accounts_from_orders();

		// List of people to email
		foreach ( $accounts as $account ) {
			if ( $account['event_scramble'] && $account['event_scramble_captain'] ) {
				$is_missing = empty( $account['name'] ) || empty( $account['contact_email'] ) || empty( $account['contact_phone'] ) || empty( $account['tshirt_size'] );

				$team = [
					['name' => $account['name'], 'email' => $account['contact_email'], 'phone' => $account['contact_phone'], 'shirt_size' => $account['tshirt_size']],
					['name' => '', 'email' => '', 'phone' => '', 'shirt_size' => ''],
					['name' => '', 'email' => '', 'phone' => '', 'shirt_size' => ''],
					['name' => '', 'email' => '', 'phone' => '', 'shirt_size' => ''],
				];

				for ( $i = 2; $i <= 4; $i++ ) {
					$member_key = "{$account['user_id']}.{$i}";
					if ( isset( $accounts[ $member_key ] ) ) {
						$member = $accounts[ $member_key ];

						$is_missing = $is_missing || empty( $member['name'] ) || empty( $member['contact_email'] ) || empty( $member['contact_phone'] ) || empty( $member['tshirt_size'] );

						$ikey = $i - 1;
						$team[$ikey]['name']       = $member['name'];
						$team[$ikey]['email']      = $member['contact_email'];
						$team[$ikey]['phone']      = $member['contact_phone'];
						$team[$ikey]['shirt_size'] = $member['tshirt_size'];
					} else {
						$is_missing = true;
					}
				}

				if ( $is_missing ) {
					$data[] = [
						'recipient'  => $account['contact_email'],
						'first_name' => get_user_meta( $account['user_id'], 'first_name', true ),
						'accounts'   => $team,
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
		return __( "We're missing info for your scramble team", 'b4b-theme-support' );
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
			'accounts'   => [
				['name' => 'Name 1', 'email' => 'test@example.com', 'phone' => '', 'shirt_size' => 'L'],
				['name' => 'Name 2', 'email' => '', 'phone' => '', 'shirt_size' => ''],
				['name' => '', 'email' => '', 'phone' => '', 'shirt_size' => ''],
				['name' => '', 'email' => '', 'phone' => '', 'shirt_size' => ''],
			],
		];


		$html = $this->get_content();

		$this->restore_locale();

		return $html;
	}

}
