<?php

namespace B4B_Theme_Support\Lib\Mail;

use Exception;
use DateTime;
use DateTimeZone;
use WP_Error;
use B4B_Theme_Support\Lib\Mail\Emails\B4B_Email_Missing_Info_100_Hole_Event;
use B4B_Theme_Support\Lib\Mail\Emails\B4B_Email_Missing_Info_Scramble_Team;
use B4B_Theme_Support\Lib\Mail\Emails\B4B_Email_Money_Raised;
use B4B_Theme_Support\Lib\Mail\Emails\B4B_Email_Payment_Past_Due;

class B4B_Mail
{

	const TABLE_NAME = 'b4b_mails';

	const TYPE_MISSING_INFO_100_HOLE_EVENT = 'missing-info-100-hole-event';
	const TYPE_MISSING_INFO_SCRAMBLE_TEAM  = 'missing-info-scramble-team';
	const TYPE_MONEY_RAISED                = 'money-raised';
	const TYPE_PAYMENT_PAST_DUE            = 'payment-past-due';

	const FREQUENCY_MANUAL  = 'manual';
	const FREQUENCY_MONTHLY = 'monthly';
	const FREQUENCY_WEEKLY  = 'weekly';
	const FREQUENCY_DAILY   = 'daily';

	const SEND_TIME = '08:00';

	const CRON_HOOK = 'b4b_mail_cron_hook';

	public function __construct()
	{
		// Init
		add_action( 'init', [ $this, 'wordpress_init' ], 10 );

		// Cron
		add_action( self::CRON_HOOK, [ $this, 'cron_run' ] );


		// Ajax
		if ( is_admin() && wp_doing_ajax() ) {
			// Save form edit
			add_action( 'wp_ajax_b4b_mail_edit_save', [ $this, 'ajax_mail_edit_save' ] );
		}


		// Admin
		if ( is_admin() && ! wp_doing_ajax() ) {
			// Admin page
			add_action( 'admin_menu', [ $this, 'mail_admin_menu' ] );

			add_action( 'admin_init', [ $this, 'try_preview_email' ] );
		}
	}

	/**
	 * Number limiter
	 * @param int $number
	 * @param int $min
	 * @param int $max
	 * @return int
	 */
	protected static function number_limit( $number, $min, $max )
	{
		return max( min( $number, $max ), $min );
	}

	/**
	 * Returns formatted price
	 * @param string $price
	 * @return string
	 */
	public static function price_format( $price )
	{
		return str_replace( '&#36;', '$', strip_tags( wc_price( $price ) ) );
	}

	/**
	 * Fires on WP init
	 */
	public function wordpress_init()
	{
		$this->try_manual_run();
	}

	/**
	 * Add link to admin menu
	 */
	public function mail_admin_menu()
	{
		add_menu_page( 'Mail', 'Auto Emails', 'manage_options', 'b4b/mail-admin-page.php', [ $this, 'mail_admin_content' ], 'dashicons-email-alt2', 2 );
	}

	/**
	 * Page content
	 */
	public function mail_admin_content()
	{
		echo wc_get_template( 'admin/mail/list.php', [
			'wp_list_table' => new B4b_Mails_List_Table(),
		], '', B4B_PLUGIN_PATH . '/templates/' );
	}

	/**
	 * Return WooCommerce mailer
	 * @param object $mail
	 * @return B4B_Email
	 * @throws Exception
	 */
	protected function get_mailer( $mail )
	{
		// Init WC mailer
		WC()->mailer();

		switch ( $mail->type ) {
			case self::TYPE_MISSING_INFO_100_HOLE_EVENT:
				$mailer = new B4B_Email_Missing_Info_100_Hole_Event( $mail );
				break;
			case self::TYPE_MISSING_INFO_SCRAMBLE_TEAM:
				$mailer = new B4B_Email_Missing_Info_Scramble_Team( $mail );
				break;
			case self::TYPE_MONEY_RAISED:
				$mailer = new B4B_Email_Money_Raised( $mail );
				break;
			case self::TYPE_PAYMENT_PAST_DUE:
				$mailer = new B4B_Email_Payment_Past_Due( $mail );
				break;
			default:
				throw new Exception( sprintf( 'Uknown mail type for (%s) mail id="%d".', $mail->type, $mail->id ) );
				// break;
		}

		return $mailer;
	}

	/**
	 * Get mail object
	 * @param int $id Email ID
	 * @return object
	 */
	protected function get_mail_object( $id )
	{
		global $wpdb;
		$mails_table = $wpdb->prefix . self::TABLE_NAME;

		// Fetch mail that needs to be sent
		$mail = $wpdb->get_row( $wpdb->prepare( "
			SELECT *
			FROM {$mails_table}
			WHERE id = %d
			LIMIT 1;
		", $id ), 'OBJECT' );
		if ( !is_object( $mail ) ) {
			throw new Exception( sprintf( 'Cannot fetch mail with id="%d" from DB.', $id ) );
		}

		return $mail;
	}

	/**
	 * Returns next send date based on frequency selected
	 * @param type $frequency_type
	 * @param type $frequency_value
	 * @return type
	 * @throws Exception
	 */
	protected static function get_next_send_date( $frequency_type, $frequency_value )
	{
		$date = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$time = self::SEND_TIME;
		switch ( $frequency_type ) {
			case self::FREQUENCY_MANUAL:
				$frequency_value = 0;
				$next_send_at = null;
				break;
			case self::FREQUENCY_MONTHLY:
				$frequency_value = self::number_limit( $frequency_value, 1, 28 );

				if ( $frequency_value <= gmdate( 'd' ) ) {
					$date->modify( '+1 month' );
				}
				$next_send_at = $date->format( "Y-m-{$frequency_value} {$time}:00" );
				break;
			case self::FREQUENCY_WEEKLY:
				$frequency_value = self::number_limit( $frequency_value, 1, 7 );

				$days = $frequency_value - $date->format( 'w' );
				if ( $days <= 0 ) {
					$days += 7;
				}
				$date->modify( "+{$days} days" );
				$next_send_at = $date->format( "Y-m-d {$time}:00" );
				break;
			case self::FREQUENCY_DAILY:
				$frequency_value = self::number_limit( $frequency_value, 1, 30 );

				$date->modify( "+{$frequency_value} days" );
				$next_send_at = $date->format( "Y-m-d {$time}:00" );
				break;
			default:
				throw new Exception( sprintf( 'Uknown frequency type: "%s".', $frequency_type ) );
				// break;
		}

		return $next_send_at;
	}

	/**
	 * Send emails
	 * @param int $id Email type ID
	 * @param bool $is_manual Is manual send i.e. no need to re-schedule
	 * @return void
	 */
	protected function send_emails( $id, $is_manual )
	{
		global $wpdb;
		$mails_table = $wpdb->prefix . self::TABLE_NAME;

		// Fetch mail that needs to be sent
		$mail = $this->get_mail_object( $id );

		$mailer = $this->get_mailer( $mail );
		$mailer->trigger_all();


		$db_new_data = [];

		// Set last sent date
		$db_new_data['last_sent_at'] = current_time( 'mysql', 1 );

		// Set new next send date
		if ( !$is_manual ) {
			$db_new_data['next_send_at'] = self::get_next_send_date( $mail->frequency_type, $mail->frequency_value );
		}

		// Update DB data
		$wpdb->update( $mails_table, $db_new_data, [ 'id' => $mail->id ] );
	}

	/**
	 * Check if manual action is called and run it if so
	 * @return void
	 */
	protected function try_manual_run()
	{
		if ( !isset( $_GET['b4b-mail-send'], $_GET['id'] ) || $_GET['b4b-mail-send'] !== '1' ) {
			return;
		}

		if ( empty( $_GET['id'] ) || !wp_verify_nonce( $_GET['_wpnonce'] ) ) {
			die( 'Security check!' );
		}

		$this->send_emails( (int) $_GET['id'], true );

		// Redirect
		wp_safe_redirect( admin_url( 'admin.php?page=b4b/mail-admin-page.php' ) );
		exit;
	}

	/**
	 * Run cron - send all emails where send date is in the past
	 *
	 * NOTE: Adds 10 minutes because WP cron does not allow scheduling an event to
	 * occur within 10 minutes of an existing event with the same action hook
	 *
	 * @return void
	 */
	public function cron_run()
	{
		global $wpdb;
		$mails_table = $wpdb->prefix . B4B_Mail::TABLE_NAME;

		// Fetch all mails that have next_send_at in the past
		$results = $wpdb->get_results( "
			SELECT *
			FROM {$mails_table}
			WHERE next_send_at IS NOT NULL AND next_send_at < (NOW() + INTERVAL 10 MINUTE)
			ORDER BY next_send_at;
		", 'ARRAY_A' );

		if ( $results !== null ) {
			foreach ( $results as $result ) {
				$this->send_emails( $result['id'], false );
			}
		}

		// Schedule next run
		$this->cron_schedule();
	}

	/**
	 * Schedule cron and remove any existing schedules
	 * @return void
	 */
	public function cron_schedule()
	{
		global $wpdb;

		// Get new schedule time (if any)
		$time = $wpdb->get_var( "
			SELECT MIN(next_send_at)
			FROM {$wpdb->prefix}b4b_mails
			WHERE next_send_at IS NOT NULL;
		" );

		// Get current schedule time (if any)
		$scheduled = wp_next_scheduled( self::CRON_HOOK );

		// NOTE: Run unschedule only if we have current schedule and it is different from new schedule time
		if ( $scheduled !== false && $scheduled !== $time ) {
			wp_unschedule_event( $scheduled, self::CRON_HOOK );
		}

		// Schedule new
		if ( $time !== null && $scheduled !== $time ) {
			wp_schedule_single_event( strtotime( $time ), self::CRON_HOOK, [] );
		}
	}

    /**
     * Save mail edit form
	 * @return void
     */
    public function ajax_mail_edit_save()
	{
		if ( !isset( $_POST['nonce_field'], $_POST['id'], $_POST['email_subject'], $_POST['frequency_type'], $_POST['frequency_value'] ) ) {
			$error = new WP_Error( 1, 'Missing POST data' );
 			wp_send_json_error( $error );
			// wp_die();
		} else if ( !wp_verify_nonce( $_POST['nonce_field'], 'b4b_mail_edit_save' ) ) {
			$error = new WP_Error( 2, 'Security error' );
 			wp_send_json_error( $error );
			// wp_die();
		}

		$data_id              = (int) $_POST['id'];
		$data_email_subject   = wp_unslash( $_POST['email_subject'] );
		$data_frequency_type  = wp_unslash( $_POST['frequency_type'] );
		$data_frequency_value = (int) wp_unslash( $_POST['frequency_value'] );

		if ( $data_id === 0 || empty( $data_email_subject ) ) {
			$error = new WP_Error( 3, 'Data error' );
 			wp_send_json_error( $error );
			// wp_die();
		}
		// TODO: additional checks

		global $wpdb;
		$mails_table = $wpdb->prefix . self::TABLE_NAME;

		$db_new_data = [
			'email_subject'   => $data_email_subject,
			'frequency_type'  => $data_frequency_type,
			'frequency_value' => $data_frequency_value,
			'updated_at'      => current_time( 'mysql', 1 ),
			'next_send_at'    => self::get_next_send_date( $data_frequency_type, $data_frequency_value ),
		];
		$result = $wpdb->update( $mails_table, $db_new_data, [ 'id' => $data_id ] );
		if ( $result === false ) {
			$error = new WP_Error( 4, 'Unable to save data to database.' );
 			wp_send_json_error( $error );
			// wp_die();
		}

		// Schedule cron
		$this->cron_schedule();

        wp_send_json( [
            'message'  => __( 'Successfully saved', 'b4b-theme-support' ),
			'redirect' => admin_url( 'admin.php?page=b4b/mail-admin-page.php' ),
        ] );
        // wp_die();
    }

	/**
	 * Preview email templates
	 * @return void
	 */
	public function try_preview_email()
	{
		if ( !isset( $_GET['b4b_preview_mail'] ) ) {
			return;
		}

		if ( ! ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'preview-mail' ) ) ) {
			die( 'Security check!' );
		}

		$mail = $this->get_mail_object( (int) $_GET['id'] );
		$mailer = $this->get_mailer( $mail );

		$message = $mailer->get_preview_html();
		$message = apply_filters( 'woocommerce_mail_content', $mailer->style_inline( $message ) );
		echo $message;
		exit;
	}

}
