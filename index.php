<?php
/**
 * Plugin Name:    Gmail Inbox Action for Wordpress
 * Author: Umesh Kumar<umeshsingla05@gmail.com>
 * Author URI:    http://codechutney.com
 * Description:    Adds one click approval option for comments in Gmail
 * Version: 0.1
 * Text Domain : wp_gia
 */
/**
 * Used for Tranlation
 */
define( 'WP_GIA_TRANSLATION_DOMAIN', 'gia_inscub' );

//Define Path constants
define( 'WP_GIA_PATH', plugin_dir_path( __FILE__ ) );

define( 'WP_GIA_APP_PATH', WP_GIA_PATH . 'app/' );

require_once( WP_GIA_APP_PATH . 'GmailInboxAction.php');

/**
 * Create a instance of GmailInboxAction class
 */
function gia_initate_class() {
    global $gmailinboxaction;
    $gmailinboxaction = new GmailInboxAction();
}

add_action( 'init', 'gia_initate_class' );
