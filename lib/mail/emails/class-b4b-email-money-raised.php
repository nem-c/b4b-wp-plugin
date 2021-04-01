<?php

namespace B4B_Theme_Support\Lib\Mail\Emails;

use WC_Product_Query;
use B4B_Theme_Support\Lib\Mail\B4B_Mail;

/**
 * An email sent to the customers when they didn't fill in all the data for 100 holes event.
 */
class B4B_Email_Money_Raised extends B4B_Email
{

	/**
	 * Fetch donations link
	 * @return string
	 */
	private function get_donation_link()
	{
		// Donation link
		$year = gmdate( 'Y' );
		$adp_query = new WC_Product_Query( [
			'status'                => 'publish',
			'type'                  => 'donation',
			'donation_opened_range' => sprintf( '%d-01-01...%d-12-31', $year, $year ),
			'posts_per_page'        => -1,
		] );

		$adps = $adp_query->get_products();
		$adp = null;
		if ( $adps >= 1 ) {
			$adp = current( $adps );
		}

		$donation_link = '';
		if ( $adp ) {
			$donation_link = get_permalink( $adp->get_id() );
		}

		return $donation_link;
	}

	/**
	 * Fetch data for sending emails
	 * @global type $wpdb
	 */
	private function get_data()
	{
		$data = [];
		$accounts = self::get_accounts_from_orders();

		// Donation link
		$donation_link = $this->get_donation_link();

		// Top 10 fundraisers
		$fundraisers = [];
		foreach ( $accounts as $account ) {
			if ( $account['event_100_holes'] ) {
				$fundraisers[] = [
					'name'       => $account['name'],
					'amount'     => B4B_Mail::price_format( $account['event_100_holes_money_raised'] ),
					'amount_raw' => $account['event_100_holes_money_raised'],
				];
			}
		}
		array_multisort( array_column( $fundraisers, 'amount_raw' ), SORT_DESC, $fundraisers );
		$fundraisers = array_slice( $fundraisers, 0, 10 );

		// List of people to email
		foreach ( $accounts as $account ) {
			if ( $account['event_100_holes'] ) {

				$b4b_name_slug = get_user_meta( $account['user_id'], 'b4b_name_slug', true );
				$for = !empty( $b4b_name_slug ) ? $b4b_name_slug : $account['user_id'];

				$data[] = [
					'recipient'     => $account['contact_email'],
					'first_name'    => get_user_meta( $account['user_id'], 'first_name', true ),
					'money_raised'  => B4B_Mail::price_format( $account['event_100_holes_money_raised'] ),
					'money_goal'    => B4B_Mail::price_format( $account['event_100_holes_donation_goal'] ),
					'year'          => gmdate( 'Y' ),
					'donation_link' => $donation_link ? $donation_link . '?for=' . $for : '',
					'fundraisers'   => $fundraisers,
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
		return __( "You've raised {MONEY_RAISED} in {YEAR}", 'b4b-theme-support' ) . '<p>' . __( 'fundraising goal {MONEY_GOAL} per golfer', 'b4b-theme-support' ) . '</p>';
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
				$this->placeholders['{YEAR}']         = $this->args['year'];
				$this->placeholders['{MONEY_RAISED}'] = $this->args['money_raised'];
				$this->placeholders['{MONEY_GOAL}']   = $this->args['money_goal'];

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

		// Donation link
		$donation_link = $this->get_donation_link();

		// Fill fake data
		$this->args = [
			'first_name'    => 'NAME',
			'money_raised'  => '$2,500',
			'money_goal'    => '$2,000',
			'year'          => gmdate( 'Y' ),
			'donation_link' => $donation_link,
			'fundraisers' => [
				['name' => 'Name 1', 'amount' => '$1,100'],
				['name' => 'Name 2', 'amount' => '$2,200'],
				['name' => 'Name 3', 'amount' => '$3,300'],
			],
		];
		$this->placeholders['{YEAR}']         = $this->args['year'];
		$this->placeholders['{MONEY_RAISED}'] = $this->args['money_raised'];
		$this->placeholders['{MONEY_GOAL}']   = $this->args['money_goal'];


		$html = $this->get_content();

		$this->restore_locale();

		return $html;
	}

}
