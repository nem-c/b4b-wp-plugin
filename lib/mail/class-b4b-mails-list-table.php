<?php

namespace B4B_Theme_Support\Lib\Mail;

use DateTime;
use DateTimeZone;
use WP_List_Table;

/**
 * Display a table with email list.
 * @see WP_List_Table
 */
class B4b_Mails_List_Table extends WP_List_Table
{
	protected $frequency_types = [];

	protected $per_page = 20;

	public function __construct()
	{
		parent::__construct( [
			'singular' => __( 'mail', 'b4b-theme-support' ),
			'plural'   => __( 'mails', 'b4b-theme-support' ),
			'ajax'     => false,
		] );

		$this->frequency_types = [
			B4B_Mail::FREQUENCY_MANUAL  => __( 'Manual', 'b4b-theme-support' ),
			B4B_Mail::FREQUENCY_MONTHLY => __( 'Monthly', 'b4b-theme-support' ),
			B4B_Mail::FREQUENCY_WEEKLY  => __( 'Weekly', 'b4b-theme-support' ),
			B4B_Mail::FREQUENCY_DAILY   => __( 'Daily', 'b4b-theme-support' ),
		];
	}

	/**
	 * Add suffix
	 * @return string
	 */
	protected static function add_ordinal_number_suffix( $num )
	{
		if ( ! in_array( ( $num % 100 ), [ 11, 12, 13 ] ) ) {
			switch ( $num % 10 ) {
				// Handle 1st, 2nd, 3rd
				case 1:
					return $num . 'st';
					// break;
				case 2:
					return $num . 'nd';
					// break;
				case 3:
					return $num . 'rd';
					// break;
			}
		}

		return $num.'th';
	}

	/**
	 * Retrieve mails from the database
	 *
	 * @param int $page_number
	 * @return mixed
	 */
	protected function get_mails( $page_number = 1 )
	{
		global $wpdb;

		$offset = ( $page_number - 1 ) * $this->per_page;

		$mails_table = $wpdb->prefix . B4B_Mail::TABLE_NAME;
		$results = $wpdb->get_results( $wpdb->prepare( "
			SELECT *
			FROM {$mails_table}
			ORDER BY id
			LIMIT %d
			OFFSET %d;
		", $this->per_page, $offset ), 'ARRAY_A' );

		return $results;
	}

	/**
	 * Returns a list of frequency types.
	 * @return array
	 */
	public function get_frequency_types()
	{
		return $this->frequency_types;
	}

	/**
	 * Returns a list of days.
	 * @return array
	 */
	public static function get_days()
	{
		return [
			1 => __( 'Monday', 'b4b-theme-support' ),
			2 => __( 'Tuesday', 'b4b-theme-support' ),
			3 => __( 'Wednesday', 'b4b-theme-support' ),
			4 => __( 'Thursday', 'b4b-theme-support' ),
			5 => __( 'Friday', 'b4b-theme-support' ),
			6 => __( 'Saturday', 'b4b-theme-support' ),
			7 => __( 'Sunday', 'b4b-theme-support' ),
		];
	}

	/**
	 * Returns send time
	 * @return string
	 */
	public static function get_send_time()
	{
		$date = new DateTime( B4B_Mail::SEND_TIME, new DateTimeZone( 'UTC' ) );
		$date->setTimezone( wp_timezone() );
		return $date->format('h:i A');
	}

	/**
	 * @inheritdoc
	 */
	public function prepare_items()
	{
		$mails = self::get_mails();

        $this->set_pagination_args( [
            'total_items' => count( $mails ),
            'per_page'    => $this->per_page
        ] );

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$this->items = $mails;
	}

	/**
	 * @return array
	 */
	public function get_columns()
	{
		return [
			'type'          => __( 'Type', 'b4b-theme-support' ),
			'email_subject' => __( 'Email Subject', 'b4b-theme-support' ),
			'last_sent_at'  => __( 'Last Sent On', 'b4b-theme-support' ),
			'next_send_at'  => __( 'Next Send Date', 'b4b-theme-support' ),
			'frequency'     => __( 'Frequency', 'b4b-theme-support' ),
			'actions'       => __( 'Send Now', 'b4b-theme-support' ),
		];
	}

	/**
	 * @return array
	 */
	protected function get_sortable_columns()
	{
		return [];
	}

	/**
	 * Get the name of the default primary column.
	 * @return string
	 */
	protected function get_default_primary_column_name()
	{
		return 'type';
	}

	/**
	 * Type column output.
	 * @param object $item The current object.
	 * @return string
	 */
	public function column_type( $item )
	{
		return sprintf(
			'<strong><a href="#" class="row-title b4b-mail-edit">%s</a></strong>',
			esc_attr( $item['type_label'] )
		);
	}

	/**
	 * Last sent date column output.
	 * @param string $item The current object.
	 * @return string
	 */
	public function column_last_sent_at( $item )
	{
		$datetime = $item['last_sent_at'];
		if ( $datetime === null ) {
			return '—';
		}

		$datetime = new DateTime( $datetime, new DateTimeZone( 'UTC' ) );
		$datetime->setTimezone( wp_timezone() );

		return '<abbr title="' . $datetime->format( __( 'Y/m/d g:i:s a' ) ) . '">' . $datetime->format( __( 'Y/m/d' ) ) . '</abbr>';
	}

	/**
	 * Next send date column output.
	 * @param string $item The current object.
	 * @return string
	 */
	public function column_next_send_at( $item )
	{
		$datetime = $item['next_send_at'];
		if ( $datetime === null ) {
			return '—';
		}

		return mysql2date( __( 'Y/m/d' ), $datetime );
	}

	/**
	 * Frequency column output.
	 * @param object $item The current object.
	 * @return string
	 */
	public function column_frequency( $item )
	{
		$frequency_label = sprintf( '<strong>%s</strong>', $this->frequency_types[ $item['frequency_type'] ] );

		switch ( $item['frequency_type'] ) {
			case B4B_Mail::FREQUENCY_MONTHLY:
				$frequency_label .= ' - ' . sprintf( __( 'On the %s', 'b4b-theme-support' ), self::add_ordinal_number_suffix( $item['frequency_value'] ) );
				break;
			case B4B_Mail::FREQUENCY_WEEKLY:
				$days = $this->get_days();
				$frequency_label .= ' - ' . sprintf( __( 'On %ss', 'b4b-theme-support' ), $days[ $item['frequency_value'] ] );
				break;
			case B4B_Mail::FREQUENCY_DAILY:
				$frequency_label .= ' - ' . sprintf( __( 'Every %d days', 'b4b-theme-support' ), $item['frequency_value'] );
				break;
		}

		return $frequency_label;
	}

	/**
	 * Actions column output.
	 * @param object $item The current object.
	 * @return string
	 */
	public function column_actions( $item )
	{
		$link = wp_nonce_url( admin_url( sprintf( 'admin.php?page=b4b/mail-admin-page.php&id=%d&b4b-mail-send=1', $item['id'] ) ) );
		return sprintf( '<strong><a href="%s" class="button b4b-send-confirmation">%s</a></strong>',
			esc_url( $link ),
			/* translators: %s: link name */
			esc_attr( __( 'Send', 'b4b-theme-support' ) )
		);
	}

	/**
	 * Handles the default column output.
	 * @return string
	 */
	public function column_default( $item, $column_name )
	{
		return $item[ $column_name ] === null ? '—' : $item[ $column_name ];
	}

	/**
	 * Generates content for a single row of the table
	 * @param object $item The current item
	 */
	public function single_row( $item ) {
		printf(
			'<tr data-id="%d" data-title="%s" data-email_subject="%s" data-frequency_type="%s" data-frequency_value="%s">',
			(int) $item['id'],
			esc_attr( $item['type_label'] ),
			esc_attr( $item['email_subject'] ),
			esc_attr( $item['frequency_type'] ),
			esc_attr( $item['frequency_value'] ),
			esc_attr( $item['type_label'] )
		);

		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * @inheritdoc
	 */
	protected function handle_row_actions( $item, $column_name, $primary )
	{
		if ( $primary !== $column_name ) {
			return '';
		}

		$actions = [];

		// Edit
		$actions['edit'] = sprintf( '<a href="#" class="b4b-mail-edit">%s</a>',
			__( 'Edit' )
		);

		// Preview
		$preview_link = wp_nonce_url( admin_url( sprintf( 'admin.php?page=b4b/mail-admin-page.php&id=%d&b4b_preview_mail=true', $item['id'] ) ), 'preview-mail' );
		$actions['view'] = sprintf( '<a href="%s" class="b4b-mail-preview" target="_blank">%s</a>',
			esc_attr( $preview_link ),
			__( 'Preview email' )
		);

		return $this->row_actions( $actions );
	}

}
