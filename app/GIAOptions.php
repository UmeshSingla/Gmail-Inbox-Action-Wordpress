<?php

/**
 * Class GIAOptions
 * 
 * Adds the instructions page for plugin
 */
class GIAOptions {
	/**
	 *Adds Instruction page
	 */
	function __construct() {
		add_action( 'admin_menu', array( $this, 'gia_admin_menu' ) );
	}

	/**
	 *Add Page
	 */
	function gia_admin_menu() {
		add_options_page( 'Google Inbox Action Instructions', 'Google Inbox Action', 'manage_options', 'google-inbox-action', array( $this, 'gia_settings_page' ) );
	}

	/**
	 * Display Page Content
	 */
	function gia_settings_page() {
		?>
		<div class="wrap">
		<h2><?php _e( 'Google Inbox Action setup instructions:' ); ?></h2>
		</div><?php
	}
}

$obj_gia_options = new GIAOptions();

