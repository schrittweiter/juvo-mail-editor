<?php


namespace JUVO_MailEditor\Mails;


use CMB2;
use JUVO_MailEditor\Mail_Generator;
use JUVO_MailEditor\Mail_Trigger_TAX;
use JUVO_MailEditor\Mails_PT;
use JUVO_MailEditor\Relay;
use JUVO_MailEditor\Trigger;
use WP_User;

class Password_Reset extends Mail_Generator {

	private $placeholders = [
		"PASSWORD_RESET_LINK" => "",
	];

	function password_reset_email_message( string $message, string $key, string $user_login, WP_User $user ): string {
		$this->setPlaceholderValues( $user, [ "key" => $key ] );
		$relay    = new Relay( $this->getTrigger(), $this->placeholders, $user );
		$template = $relay->getPosts()[0];

		return $relay->prepareContent( $template );
	}

	protected function setPlaceholderValues( WP_User $user, array $options = [] ): void {
		if ( ! empty( $options ) && isset( $options["key"] ) ) {
			$this->placeholders["PASSWORD_RESET_LINK"] = '<a href="' . network_site_url( "wp-login.php?action=rp&key={$options["key"]}&login=" . rawurlencode( $user->user_login ), 'login' ) . '">' . network_site_url( "wp-login.php?action=rp&key={$options["key"]}&login=" . rawurlencode( $user->user_login ), 'login' ) . '</a>';
		}
	}

	public function getTrigger(): string {
		return "password_reset";
	}

	function password_reset_email_subject( string $title, string $user_login, WP_User $user ): string {
		$this->setPlaceholderValues( $user );
		$relay    = new Relay( $this->getTrigger(), $this->placeholders, $user );
		$template = $relay->getPosts()[0];

		return $relay->prepareSubject( $template );

	}

	public function addCustomFields( CMB2 $cmb ): CMB2 {
		if ( has_term( $this->getTrigger(), Mail_Trigger_TAX::TAXONOMY_NAME, $cmb->object_id() ) ) {
			$cmb->remove_field( Mails_PT::POST_TYPE_NAME . '_recipients' );
		}

		return $cmb;
	}

	/**
	 * @param array $triggers
	 *
	 * @return Trigger[]
	 */
	public function registerTrigger( array $triggers ): array {

		$message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Site Name: %s' ), "{{SITE_NAME}}" ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s' ), "{{USERNAME}}" ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, ignore this email and nothing will happen.' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
		$message .= __( '{{PASSWORD_RESET_LINK}}' ) . "\r\n\r\n";

		$trigger = new Trigger( "Retrieve Password (User)", $this->getTrigger() );
		$trigger
			->setSubject( "New User Registration" )
			->setContent( $message )
			->setRecipients( "{{CONTEXT}}" )
			->setPlaceholders( $this->placeholders );

		$triggers[] = $trigger;

		return $triggers;

	}
}
