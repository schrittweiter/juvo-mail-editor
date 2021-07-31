<?php


namespace JUVO_MailEditor;


class Mail_Trigger_TAX {

	public const TAXONOMY_NAME = "juvo-mail-trigger";

	public function registerTaxonomy() {

		$labels = array(
			'name'              => _x( 'Triggers', 'taxonomy general name' ),
			'singular_name'     => _x( 'Trigger', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Triggers' ),
			'all_items'         => __( 'All Triggers' ),
			'parent_item'       => __( 'Parent Trigger' ),
			'parent_item_colon' => __( 'Parent Trigger:' ),
			'edit_item'         => __( 'Edit Trigger' ),
			'update_item'       => __( 'Update Trigger' ),
			'add_new_item'      => __( 'Add New Trigger' ),
			'new_item_name'     => __( 'New Trigger Name' ),
			'menu_name'         => __( 'Triggers' ),
		);

		register_taxonomy( self::TAXONOMY_NAME, Mails_PT::POST_TYPE_NAME, array(
			'public'            => false,
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'capabilities'      => array(
				'manage_terms' => false,
				'edit_terms'   => false,
				'delete_terms' => false
			),
		) );

	}

	public function addMetaboxes() {

		$cmb = new_cmb2_box( array(
			'id'           => self::TAXONOMY_NAME . '_metabox',
			'title'        => __( 'Mail Settings', 'juvo-mail-editor' ),
			'object_types' => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
			'taxonomies'   => array( self::TAXONOMY_NAME ),
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true, // Show field names on the left
		) );

		$cmb->add_field( array(
			'name' => __( 'Always use trigger', 'juvo-mail-editor' ),
			'desc' => __( 'Use trigger even if no post is associated with it', 'juvo-mail-editor' ),
			'id'   => self::TAXONOMY_NAME . '_always_send',
			'type' => 'checkbox'
		) );

		$cmb->add_field( array(
			'name' => __( 'Default Recipients', 'juvo-mail-editor' ),
			'desc' => __( 'Default recipients of the mail', 'juvo-mail-editor' ),
			'id'   => self::TAXONOMY_NAME . '_default_recipients',
			'type' => 'text'
		) );

		$cmb->add_field( array(
			'name' => __( 'Default Subject', 'juvo-mail-editor' ),
			'desc' => __( 'Default subject of the mail', 'juvo-mail-editor' ),
			'id'   => self::TAXONOMY_NAME . '_default_subject',
			'type' => 'text'
		) );

		$cmb->add_field( array(
			'name' => __( 'Default Content', 'juvo-mail-editor' ),
			'desc' => __( 'Default content of the mail', 'juvo-mail-editor' ),
			'id'   => self::TAXONOMY_NAME . '_default_content',
			'type' => 'textarea'
		) );

		$cmb->add_field( array(
			'name' => __( 'Additional Placeholders', 'juvo-mail-editor' ),
			'id'   => self::TAXONOMY_NAME . '_placeholders',
			'type' => 'textarea'
		) );
	}

	public function registerTrigger() {

		$triggers = [];

		$triggers = apply_filters( "juvo_mail_editor_trigger", $triggers );

		foreach ( $triggers as $trigger ) {

			if ( ! $trigger instanceof Trigger ) {
				error_log( "[juvo_mail_editor]: Provided trigger is no instance of JUVO_MailEditor\Trigger and therefore skipped" );
				continue;
			}

			$term = get_term_by( 'slug', $trigger->getSlug(), self::TAXONOMY_NAME );

			$term_id = null;
			if ( ! $term ) {
				$term_id = wp_insert_term( $trigger->getName(), self::TAXONOMY_NAME, [ "slug" => $trigger->getSlug() ] )["term_id"];
			} else {
				$term_id = $term->term_id;
			}

			// Verify Slug or mail
			if ( empty( $trigger->getName() ) || empty( $trigger->getSlug() ) ) {
				error_log( "[juvo_mail_editor]: Slug or Name are empty" );
				continue;
			}

			$term_id = wp_update_term( $term_id, self::TAXONOMY_NAME, array(
				'name' => $trigger->getName(),
				'slug' => $trigger->getSlug()
			) )["term_id"];

			update_term_meta( $term_id, self::TAXONOMY_NAME . "_always_send", $trigger->isAlwaysSent() );
			update_term_meta( $term_id, self::TAXONOMY_NAME . "_default_recipients", $trigger->getRecipients() );
			update_term_meta( $term_id, self::TAXONOMY_NAME . "_default_subject", $trigger->getSubject() );
			update_term_meta( $term_id, self::TAXONOMY_NAME . "_default_content", $trigger->getContent() );
			update_term_meta( $term_id, self::TAXONOMY_NAME . "_placeholders", $trigger->getPlaceholders() );

		}

	}

}
