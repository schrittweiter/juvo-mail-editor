<?php

namespace JUVO_MailEditor\Mails;

use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Mails_PT;
use JUVO_MailEditor\Relay;
use JUVO_MailEditor\Trigger;
use WP_User;

class New_User_Admin extends Mail_Generator {

	private $placeholders = [];

	function new_user_notification_email_admin( array $email, WP_User $user ) {

		$this->setPlaceholderValues( $user );

		$relay            = new Relay( $this->getTrigger(), $this->placeholders, $user );
		$email["to"]      = $relay->prepareRecipients();
		$email["subject"] = $relay->prepareSubject();
		$email["message"] = $relay->prepareContent();

		return $email;
	}

	protected function setPlaceholderValues( WP_User $user ): void {
	}

	/**
	 * @return string
	 */
	public function getTrigger(): string {
		return "new_user_admin";
	}

	/**
	 * @param CMB2 $cmb
	 *
	 * @return CMB2
	 */
	public function addCustomFields( CMB2 $cmb ): CMB2 {

		$field = array(
			'name' => __( 'Trigger on rest', 'juvo-mail-editor' ),
			'desc' => __( 'Sends email if user is created via Rest API', 'juvo-mail-editor' ),
			'id'   => Mails_PT::POST_TYPE_NAME . '_rest',
			'type' => 'checkbox',
		);

		return $this->addFieldForTrigger( $field, $cmb );
	}

	/**
	 * @param array $triggers
	 *
	 * @return Trigger[]
	 */
	public function registerTrigger( array $triggers ): array {

		$message = sprintf( __( 'New user registration on your site %s:' ), "{{SITE_NAME}}" ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s' ), "{{USERNAME}}" ) . "\r\n\r\n";
		$message .= sprintf( __( 'Email: %s' ), "{{USER_EMAIL}}" ) . "\r\n";

		$trigger = new Trigger( __( "New User (Admin)", 'juvo-mail-editor' ), $this->getTrigger() );
		$trigger
			->setAlwaysSent( true )
			->setSubject( sprintf( __( "%s New User Registration" ), "{{SITE_NAME}}" ) )
			->setContent( $message )
			->setRecipients( "{{ADMIN_EMAIL}}" )
			->setPlaceholders( $this->placeholders );

		$triggers[] = $trigger;

		return $triggers;
	}

	/**
	 * @param WP_User $user
	 */
	public function rest_user_create( WP_User $user ): void {

		$user_id = $user->ID;
		wp_send_new_user_notifications( $user_id, "admin" );

	}
}
